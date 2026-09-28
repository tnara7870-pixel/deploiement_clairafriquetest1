<?php

namespace App\Services;

use App\DTO\CommandeData;
use App\Events\CommandeConfirmee;
use App\Exceptions\StockInsuffisantException;
use App\Models\Article;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Livraison;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\StockPointVente;
use App\Repositories\CommandeRepository;
use App\Repositories\PanierRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommandeCreationService
{
    public function __construct(
        private PanierRepository $panierRepository,
        private CommandeRepository $commandeRepository
    ) {}

    public function createFromPanier(CommandeData $data): ?Commande
    {
        $panier = $this->panierRepository->findByUser($data->userId);
        if (! $panier || $panier->lignePaniers->isEmpty()) {
            return null;
        }

        // Point de vente dont le stock est réellement décrémenté par cette
        // commande, et qui en assurera le suivi (dashboards res.stock /
        // res.commande) : le point choisi par le client pour un retrait en
        // boutique, l'entrepôt principal configuré pour une livraison à
        // domicile (qui n'est rattachée à aucun point choisi par le
        // client). Calculé une seule fois ici et non recalculé plus tard,
        // pour rester stable même si la config change après coup.
        $pointVenteAttribue = $data->modeLivraison === 'boutique'
            ? $data->pointVente
            : config('pointvente.entrepot_principal');

        // Vérification préalable, hors verrou : pure optimisation UX pour
        // rejeter tout de suite un panier manifestement plus gros que le
        // stock, sans passer par une transaction. Elle NE remplace PAS la
        // revérification sous verrou ci-dessous — un stock lu sans verrou
        // ici peut avoir changé entre cette lecture et la création réelle
        // de la commande (cf. correctif du bug critique 1, audit du
        // 04/08/2026). Vérifiée sur le stock du point attribué, pas sur le
        // total global : un article épuisé à UCAD mais disponible au
        // Centre-ville ne doit pas bloquer une commande qui tape sur le
        // Centre-ville.
        foreach ($panier->lignePaniers as $ligne) {
            if ($ligne->article->stockPour($pointVenteAttribue) < $ligne->quantite) {
                Log::warning('Stock insuffisant avant création de commande', [
                    'idArticle' => $ligne->idArticle,
                    'quantite' => $ligne->quantite,
                    'pointVente' => $pointVenteAttribue,
                ]);
                throw StockInsuffisantException::pour(
                    $ligne->article,
                    $ligne->quantite,
                    $pointVenteAttribue,
                    $data->modeLivraison
                );
            }
        }

        $commande = null;

        try {
            DB::transaction(function () use ($panier, $data, $pointVenteAttribue, &$commande) {
                $commande = $this->commandeRepository->createWithNumero([
                    'montantTotal' => $data->total,
                    'statut' => 'en_attente',
                    'idUtilisateur' => $data->userId,
                ]);

                foreach ($panier->lignePaniers as $ligne) {
                    // Revérification SOUS VERROU, juste avant le décrément :
                    // c'est elle, et non la lecture préalable plus haut, qui
                    // constitue la véritable protection anti-survente. Sans
                    // ça, deux clients commandant simultanément le dernier
                    // exemplaire d'un article passaient tous les deux la
                    // vérification préalable (qui les voit chacun comme
                    // seuls), et le second décrémentait quand même après le
                    // premier — stock négatif, deux commandes honorées pour
                    // un seul exemplaire réel. Symétrique à la garde déjà en
                    // place dans PaiementDomainService::creerCommandeDepuisInvoice().
                    //
                    // Le verrou porte maintenant sur la ligne de stock DU
                    // POINT ATTRIBUÉ (stocks_points_vente), pas sur
                    // l'article lui-même : c'est elle qui contient la
                    // quantité réellement disponible pour ce point.
                    $article = Article::find($ligne->idArticle);

                    $stockPoint = StockPointVente::where('idArticle', $ligne->idArticle)
                        ->where('pointVente', $pointVenteAttribue)
                        ->lockForUpdate()
                        ->first();

                    if (! $article || ! $stockPoint || $stockPoint->quantiteStock < $ligne->quantite) {
                        throw StockInsuffisantException::pour(
                            $article,
                            $ligne->quantite,
                            $pointVenteAttribue,
                            $data->modeLivraison
                        );
                    }

                    LigneCommande::create([
                        'idCommande' => $commande->idCommande,
                        'idArticle' => $ligne->idArticle,
                        'quantite' => $ligne->quantite,
                        'prixUnitaire' => $ligne->article->prix,
                    ]);

                    $stockPoint->decrement('quantiteStock', $ligne->quantite);
                    $article->resynchroniserQuantiteTotale();

                    MouvementStock::create([
                        'typeMouvement' => 'sortie',
                        'idArticle' => $ligne->idArticle,
                        'pointVente' => $pointVenteAttribue,
                        'quantite' => -$ligne->quantite,
                        'motif' => 'Commande '.$commande->numeroCommande,
                        'idUtilisateur' => $data->userId,
                        'idCommande' => $commande->idCommande,
                    ]);
                }

                Livraison::create([
                    'idCommande' => $commande->idCommande,
                    'modeLivraison' => $data->modeLivraison,
                    'pointVente' => $data->pointVente,
                    'pointVenteAttribue' => $pointVenteAttribue,
                    'adresseLivraison' => $data->adresse,
                    'latitude' => $data->latitude,
                    'longitude' => $data->longitude,
                    'statutLivraison' => 'preparee',
                ]);

                Paiement::create([
                    'idCommande' => $commande->idCommande,
                    'modePaiement' => $data->modePaiement,
                    'montant' => $data->total,
                    'statutPaiement' => $data->modePaiement === 'especes' ? 'en_attente' : 'valide',
                ]);

                $panier->lignePaniers()->delete();
                $panier->update([
                    'paydunyaTokenEnAttente' => null,
                    'paydunyaInvoiceUrlEnAttente' => null,
                    'paydunyaTokenExpireA' => null,
                    'paydunyaModePaiementEnAttente' => null,
                ]);
            });

            if ($commande) {

                try {
                    event(new CommandeConfirmee($commande));
                } catch (\Throwable $e) {
                    Log::error('Commande créée avec succès mais échec de l\'envoi de la notification de confirmation', [
                        'idCommande' => $commande->idCommande,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } catch (StockInsuffisantException $e) {

            Log::warning('Commande espèces refusée : stock insuffisant à la revérification', [
                'idUtilisateur' => $data->userId,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (QueryException $e) {
            Log::error('Échec création commande', ['message' => $e->getMessage()]);

            return null;
        }

        return $commande;
    }
}
