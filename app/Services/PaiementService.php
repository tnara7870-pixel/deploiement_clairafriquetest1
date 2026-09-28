<?php

namespace App\Services;

use App\Jobs\SendCommandeConfirmeeEmail;
use App\Models\Article;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Livraison;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Panier;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Paydunya\Checkout\CheckoutInvoice;
use Paydunya\Checkout\Store;
use Paydunya\Setup;

class PaiementService
{
    private const PAYDUNYA_TOKEN_VALIDITE_MINUTES = 30;

    public function getInvoiceUrlPourPanier(Panier $panier, array $session): string
    {
        $modePaiementChoisi = in_array($session['modePaiement'] ?? null, ['wave', 'orange_money'], true)
            ? $session['modePaiement']
            : 'wave';

        if (
            $panier->paydunyaTokenEnAttente
            && $panier->paydunyaInvoiceUrlEnAttente
            && $panier->paydunyaTokenExpireA
            && $panier->paydunyaTokenExpireA->isFuture()
            && $panier->paydunyaModePaiementEnAttente === $modePaiementChoisi
        ) {
            Log::info('Réutilisation d\'une facture PayDunya déjà en attente pour ce panier', [
                'idPanier' => $panier->idPanier,
                'token' => $panier->paydunyaTokenEnAttente,
            ]);

            session(['paydunya_token' => $panier->paydunyaTokenEnAttente]);

            return $panier->paydunyaInvoiceUrlEnAttente;
        }

        $this->configurerPayDunya();

        $invoice = new CheckoutInvoice;

        foreach ($panier->lignePaniers as $ligne) {
            $invoice->addItem(
                $ligne->article->designation,
                $ligne->quantite,
                $ligne->article->prix,
                $ligne->sousTotal()
            );
        }

        $invoice->setTotalAmount($session['total']);
        $invoice->setDescription('Commande Clairafrique');

        $invoice->addCustomData('idUtilisateur', $panier->idUtilisateur);
        $invoice->addCustomData('modeLivraison', $session['modeLivraison']);
        $invoice->addCustomData('adresse', $session['adresse'] ?? '');
        $invoice->addCustomData('total', $session['total']);
        $invoice->addCustomData('fraisLivraison', $session['fraisLivraison'] ?? 0);
        $invoice->addCustomData('lignes', $panier->lignePaniers->map(function ($ligne) {
            return [
                'idArticle' => $ligne->idArticle,
                'quantite' => $ligne->quantite,
                'prixUnitaire' => $ligne->article->prix,
                'sousTotal' => $ligne->sousTotal(),
            ];
        })->toArray());
        $invoice->addCustomData('modePaiement', $modePaiementChoisi);

        $invoice->addChannel($modePaiementChoisi === 'orange_money' ? 'orange-money-senegal' : 'wave');

        if (! $invoice->create()) {
            Log::error('PayDunya création facture échouée: '.$invoice->response_text);

            return '';
        }

        $panier->update([
            'paydunyaTokenEnAttente' => $invoice->token,
            'paydunyaInvoiceUrlEnAttente' => $invoice->getInvoiceUrl(),
            'paydunyaTokenExpireA' => now()->addMinutes(self::PAYDUNYA_TOKEN_VALIDITE_MINUTES),
            'paydunyaModePaiementEnAttente' => $modePaiementChoisi,
        ]);

        session(['paydunya_token' => $invoice->token]);

        return $invoice->getInvoiceUrl();
    }

    public function confirmerInvoice(string $token): ?CheckoutInvoice
    {
        $this->configurerPayDunya();

        $invoice = new CheckoutInvoice;
        $confirmed = $invoice->confirm($token);

        if (! $confirmed || $invoice->getStatus() !== 'completed') {
            Log::warning('PayDunya confirmation échouée ou facture non complétée', [
                'token' => $token,
                'status' => $invoice->getStatus(),
                'response' => $invoice->response_text ?? null,
            ]);

            return null;
        }

        return $invoice;
    }

    public function confirmerToken(string $token): ?Commande
    {
        $invoice = $this->confirmerInvoice($token);
        if (! $invoice) {
            return null;
        }

        return $this->creerCommandeDepuisInvoice($invoice, $token);
    }

    public function creerCommandeDepuisInvoice(
        CheckoutInvoice $invoice,
        string $token
    ): ?Commande {
        if (! empty($token)) {
            $paiementExistant = Paiement::where('referenceTransaction', $token)->first();
            if ($paiementExistant) {
                Log::warning('⚠️ PayDunya Token DÉJÀ UTILISÉ en BDD — Redirection vers la commande existante', [
                    'token' => $token,
                    'commande_id' => $paiementExistant->idCommande,
                ]);

                return $paiementExistant->commande;
            }
        }

        $userId = $invoice->getCustomData('idUtilisateur');
        $modeLivraison = $invoice->getCustomData('modeLivraison');
        $adresse = $invoice->getCustomData('adresse');
        $total = $invoice->getCustomData('total');
        $rawLignes = $invoice->getCustomData('lignes');
        $modePaiement = in_array($invoice->getCustomData('modePaiement'), ['wave', 'orange_money'], true)
            ? $invoice->getCustomData('modePaiement')
            : 'wave';

        $lignes = [];
        if (is_string($rawLignes)) {
            $lignes = json_decode($rawLignes, true) ?? [];
        } elseif (is_array($rawLignes)) {
            $lignes = $rawLignes;
        }

        Log::info('DIAG creerCommandeDepuisInvoice() — Données récupérées de la facture PayDunya', [
            'token' => $token,
            'userId' => $userId,
            'modeLivraison' => $modeLivraison,
            'total' => $total,
            'nombre_lignes' => count($lignes),
        ]);

        if (! $userId || empty($lignes) || ! is_array($lignes)) {
            Log::error('❌ IMPOSSIBLE DE CRÉER LA COMMANDE : custom_data PayDunya manquants ou invalides', [
                'token' => $token,
                'userId' => $userId,
                'rawLignes' => $rawLignes,
            ]);

            return null;
        }

        $commande = null;

        try {
            DB::transaction(function () use (
                $lignes, $userId, $modeLivraison,
                $adresse, $total, $token, $modePaiement, &$commande
            ) {
                $commande = Commande::creerAvecNumero([
                    'montantTotal' => $total,
                    'statut' => 'validee',
                    'idUtilisateur' => $userId,
                ]);

                $problemes = [];

                foreach ($lignes as $ligne) {
                    LigneCommande::create([
                        'idCommande' => $commande->idCommande,
                        'idArticle' => $ligne['idArticle'],
                        'quantite' => $ligne['quantite'],
                        'prixUnitaire' => $ligne['prixUnitaire'],
                    ]);

                    $article = Article::lockForUpdate()->find($ligne['idArticle']);

                    if ($article && $article->quantiteStock >= $ligne['quantite']) {
                        $article->decrement('quantiteStock', $ligne['quantite']);

                        MouvementStock::create([
                            'typeMouvement' => 'sortie',
                            'idArticle' => $ligne['idArticle'],
                            'quantite' => -$ligne['quantite'],
                            'motif' => 'Commande '.$commande->numeroCommande,
                            'idUtilisateur' => $userId,
                            'idCommande' => $commande->idCommande,
                        ]);
                    } else {
                        $problemes[] = sprintf(
                            '%s : %d demandé(s), %d disponible(s)',
                            $article->designation ?? ('article #'.$ligne['idArticle']),
                            $ligne['quantite'],
                            $article->quantiteStock ?? 0
                        );

                        Log::warning('Stock insuffisant lors de la confirmation de paiement', [
                            'idArticle' => $ligne['idArticle'],
                            'quantiteDemandee' => $ligne['quantite'],
                            'idCommande' => $commande->idCommande,
                        ]);
                    }
                }

                if (! empty($problemes)) {
                    $commande->update([
                        'problemeStock' => true,
                        'problemeStockDetails' => implode(' | ', $problemes),
                    ]);
                }

                Livraison::create([
                    'idCommande' => $commande->idCommande,
                    'modeLivraison' => $modeLivraison,
                    'adresseLivraison' => $adresse ?: null,
                    'statutLivraison' => 'preparee',
                ]);

                Paiement::create([
                    'idCommande' => $commande->idCommande,
                    'modePaiement' => $modePaiement,
                    'montant' => $total,
                    'statutPaiement' => 'valide',
                    'referenceTransaction' => $token,
                ]);

                $panier = Panier::where('idUtilisateur', $userId)->first();
                if ($panier) {
                    $panier->lignePaniers()->delete();
                    $panier->update([
                        'paydunyaTokenEnAttente' => null,
                        'paydunyaInvoiceUrlEnAttente' => null,
                        'paydunyaTokenExpireA' => null,
                        'paydunyaModePaiementEnAttente' => null,
                    ]);
                }
            });

            Log::info('✅ NOUVELLE COMMANDE CRÉÉE AVEC SUCCÈS !', [
                'commande_id' => $commande->idCommande ?? null,
                'numero' => $commande->numeroCommande ?? null,
            ]);

            SendCommandeConfirmeeEmail::dispatch($commande);
        } catch (QueryException $e) {
            $paiementExistant = Paiement::where('referenceTransaction', $token)->first();

            if ($paiementExistant) {
                return $paiementExistant->commande;
            }

            Log::error('DIAG — Échec création de commande (QueryException)', [
                'token' => $token,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::error('DIAG — Exception durant la création de la commande', [
                'token' => $token,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $commande;
    }

    public function configurerPayDunya(): void
    {
        Setup::setMasterKey(config('paydunya.master_key'));
        Setup::setPublicKey(config('paydunya.public_key'));
        Setup::setPrivateKey(config('paydunya.private_key'));
        Setup::setToken(config('paydunya.token'));
        Setup::setMode(config('paydunya.mode'));

        Store::setName(config('paydunya.store.name'));
        Store::setTagline(config('paydunya.store.tagline'));
        Store::setPhoneNumber(config('paydunya.store.phone_number'));
        Store::setPostalAddress(config('paydunya.store.postal_address'));
        Store::setWebsiteUrl(config('paydunya.store.website_url'));
        Store::setCancelUrl(config('paydunya.cancel_url'));
        Store::setReturnUrl(config('paydunya.return_url'));
        Store::setCallbackUrl(config('paydunya.ipn_url'));
    }
}
