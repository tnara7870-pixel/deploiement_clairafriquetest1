<?php

namespace App\Services;

use App\Events\CommandeConfirmee;
use App\Models\Article;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Livraison;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Panier;
use App\Models\StockPointVente;
use App\Repositories\PaiementRepository;
use App\Repositories\PanierRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Paydunya\Checkout\CheckoutInvoice;
use Paydunya\Checkout\Store;
use Paydunya\Setup;

class PaiementDomainService
{
    private const PAYDUNYA_TOKEN_VALIDITE_MINUTES = 30;

    public function __construct(
        private PanierRepository $panierRepository,
        private PaiementRepository $paiementRepository,
    ) {}

    public function getInvoiceUrlForUser(int $userId, array $session): ?string
    {
        $panier = $this->panierRepository->findByUserForUpdate($userId);

        if (! $panier || $panier->lignePaniers->isEmpty()) {
            return null;
        }

        return $this->getInvoiceUrlPourPanier($panier, $session);
    }

    public function getInvoiceUrlPourPanier(Panier $panier, array $session): string
    {
        $modePaiementChoisi = $this->resolveModePaiement($session);

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
        $invoice->addCustomData('pointVente', $session['pointVente'] ?? '');
        $invoice->addCustomData('adresse', $session['adresse'] ?? '');
        $invoice->addCustomData('latitude', $session['latitude'] ?? '');
        $invoice->addCustomData('longitude', $session['longitude'] ?? '');
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

        $invoice->addChannel($modePaiementChoisi === 'orange_money' ? 'orange-money-senegal' : 'wave-senegal');

        if (! $invoice->create()) {
            Log::error('PayDunya création facture échouée: '.$invoice->response_text);

            return '';
        }

        $this->paiementRepository->updatePanierPaydunyaData($panier, [
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

        // Ce chemin est emprunté par retour() ET ipn() — c'est-à-dire
        // précisément le moment où le client a potentiellement déjà payé.
        // $invoice->confirm() peut échouer de deux façons très
        // différentes : soit en renvoyant false/un statut incomplet (déjà
        // géré ci-dessous), soit en LEVANT une exception (timeout réseau,
        // panne temporaire de l'API PayDunya). Avant ce correctif, seul
        // le premier cas était couvert — le second remontait comme une
        // page d'erreur brute pour un client qui venait pourtant de
        // payer. Voir aussi PaiementController::choix(), qui avait
        // exactement le même trou côté création de facture.
        try {
            $confirmed = $invoice->confirm($token);
        } catch (\Throwable $e) {
            Log::error('Exception PayDunya lors de la confirmation de facture', [
                'token' => $token,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

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
            $paiementExistant = $this->paiementRepository->findPaymentByToken($token);
            if ($paiementExistant) {
                Log::warning('Paiement.token_deja_utilise : redirection vers la commande existante', [
                    'token' => $token,
                    'commande_id' => $paiementExistant->idCommande,
                ]);

                return $paiementExistant->commande;
            }
        }

        $userId = $invoice->getCustomData('idUtilisateur');
        $modeLivraison = $invoice->getCustomData('modeLivraison');
        $pointVente = $invoice->getCustomData('pointVente') ?: null;
        $adresse = $invoice->getCustomData('adresse');
        $latitude = $invoice->getCustomData('latitude') ?: null;
        $longitude = $invoice->getCustomData('longitude') ?: null;
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

        Log::info('Paiement.creation_commande.donnees_facture', [
            'token' => $token,
            'userId' => $userId,
            'modeLivraison' => $modeLivraison,
            'total' => $total,
            'nombre_lignes' => count($lignes),
        ]);

        if (! $userId || empty($lignes) || ! is_array($lignes)) {
            Log::error('Paiement.creation_commande.custom_data_invalides', [
                'token' => $token,
                'userId' => $userId,
                'rawLignes' => $rawLignes,
            ]);

            return null;
        }

        $montantConfirme = (float) $invoice->getTotalAmount();
        if (abs($montantConfirme - (float) $total) > 0.01) {
            Log::error('Paiement.creation_commande.incoherence_montant', [
                'token' => $token,
                'montantAttendu' => $total,
                'montantConfirme' => $montantConfirme,
            ]);

            return null;
        }

        // Même logique que CommandeCreationService::createFromPanier() :
        // le point de vente dont le stock est réellement décrémenté et qui
        // assure le suivi de cette commande.
        $pointVenteAttribue = $modeLivraison === 'boutique'
            ? $pointVente
            : config('pointvente.entrepot_principal');

        $commande = null;

        try {
            DB::transaction(function () use (
                $lignes, $userId, $modeLivraison, $pointVente, $pointVenteAttribue,
                $adresse, $latitude, $longitude, $total, $token, $modePaiement, &$commande
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

                    $article = Article::find($ligne['idArticle']);

                    $stockPoint = StockPointVente::where('idArticle', $ligne['idArticle'])
                        ->where('pointVente', $pointVenteAttribue)
                        ->lockForUpdate()
                        ->first();

                    if ($article && $stockPoint && $stockPoint->quantiteStock >= $ligne['quantite']) {
                        $stockPoint->decrement('quantiteStock', $ligne['quantite']);
                        $article->resynchroniserQuantiteTotale();

                        MouvementStock::create([
                            'typeMouvement' => 'sortie',
                            'idArticle' => $ligne['idArticle'],
                            'pointVente' => $pointVenteAttribue,
                            'quantite' => -$ligne['quantite'],
                            'motif' => 'Commande '.$commande->numeroCommande,
                            'idUtilisateur' => $userId,
                            'idCommande' => $commande->idCommande,
                        ]);
                    } else {
                        $problemes[] = sprintf(
                            '%s : %d demandé(s), %d disponible(s) sur ce point de vente',
                            $article->designation ?? ('article #'.$ligne['idArticle']),
                            $ligne['quantite'],
                            $stockPoint->quantiteStock ?? 0
                        );

                        Log::warning('Stock insuffisant lors de la confirmation de paiement', [
                            'idArticle' => $ligne['idArticle'],
                            'quantiteDemandee' => $ligne['quantite'],
                            'idCommande' => $commande->idCommande,
                            'pointVente' => $pointVenteAttribue,
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
                    'pointVente' => $pointVente,
                    'pointVenteAttribue' => $pointVenteAttribue,
                    'adresseLivraison' => $adresse ?: null,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'statutLivraison' => 'preparee',
                ]);

                Paiement::create([
                    'idCommande' => $commande->idCommande,
                    'modePaiement' => $modePaiement,
                    'montant' => $total,
                    'statutPaiement' => 'valide',
                    'referenceTransaction' => $token,
                ]);

                $panier = $this->panierRepository->findByUser($userId);
                if ($panier) {
                    $this->panierRepository->clearContents($panier);
                    $this->panierRepository->clearPaydunyaData($panier);
                }
            });

            Log::info('Paiement.creation_commande.succes', [
                'commande_id' => $commande->idCommande ?? null,
                'numero' => $commande->numeroCommande ?? null,
            ]);

            if ($commande) {
                // Même isolation que dans CommandeCreationService::createFromPanier() :
                // la transaction ci-dessus est déjà validée, la commande existe
                // réellement en base. Une erreur d'envoi de notification ne doit
                // plus jamais pouvoir être interprétée comme un échec de paiement
                // par le catch(Throwable) ci-dessous (qui, ici, a de toute façon
                // un filet de sécurité via findPaymentByToken() — mais autant ne
                // pas en dépendre pour un problème qui n'a rien à voir avec le
                // paiement lui-même). Voir audit du 06/08/2026.
                try {
                    event(new CommandeConfirmee($commande));
                } catch (\Throwable $e) {
                    Log::error('Commande créée avec succès mais échec de l\'envoi de la notification de confirmation', [
                        'idCommande' => $commande->idCommande,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            $paiementExistant = $this->paiementRepository->findPaymentByToken($token);

            if ($paiementExistant) {
                return $paiementExistant->commande;
            }

            Log::error('Paiement.creation_commande.exception', [
                'token' => $token,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $commande;
    }

    public function annulerPaiementEnCours(int $userId): void
    {
        $panier = $this->panierRepository->findByUser($userId);
        if ($panier) {
            $this->panierRepository->clearPaydunyaData($panier);
        }
    }

    private function resolveModePaiement(array $session): string
    {
        return in_array($session['modePaiement'] ?? null, ['wave', 'orange_money'], true)
            ? $session['modePaiement']
            : 'wave';
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
