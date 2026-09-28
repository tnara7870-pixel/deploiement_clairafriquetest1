<?php

namespace App\Console\Commands;

use App\Exceptions\CommandeAnnulationException;
use App\Models\Commande;
use App\Services\CommandeAnnulationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sans cette commande, une commande "espèces" que le client ne vient
 * jamais honorer en boutique restait indéfiniment au statut "en_attente"
 * — avec son stock bloqué en permanence (Article::quantiteStock déjà
 * décrémenté à la création, voir CommandeCreationService), sans aucune
 * action automatique pour le libérer. Sur un catalogue à faible
 * profondeur de stock, quelques commandes espèces jamais honorées
 * suffisaient à bloquer artificiellement des ventes. Voir audit du
 * 04/08/2026, point moyen 10.
 *
 * Chaque commande expirée est traitée indépendamment (une erreur sur
 * l'une n'empêche pas le traitement des suivantes) et réutilise
 * exactement le même chemin d'annulation que l'échec de paiement
 * constaté manuellement par un administrateur
 * (CommandeAnnulationService::annulerPourEchecPaiement), avec un acteur
 * "système" (null) plutôt qu'un utilisateur humain fictif — voir
 * migration mouvements_stock (idUtilisateur nullable).
 */
class ExpirerCommandesEspecesNonHonorees extends Command
{
    protected $signature = 'commandes:expirer-especes';

    protected $description = 'Annule et restitue le stock des commandes en paiement espèces jamais honorées après le délai configuré';

    public function __construct(private CommandeAnnulationService $commandeAnnulationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $delaiHeures = (int) config('commande.delai_expiration_especes_heures');
        $seuil = now()->subHours($delaiHeures);

        $commandesExpirees = Commande::query()
            ->where('statut', 'en_attente')
            ->whereHas('paiement', fn ($q) => $q->where('modePaiement', 'especes'))
            ->where('dateCommande', '<', $seuil)
            ->get();

        if ($commandesExpirees->isEmpty()) {
            $this->info('Aucune commande espèces à expirer.');

            return self::SUCCESS;
        }

        $nbAnnulees = 0;
        $nbEchecs = 0;

        foreach ($commandesExpirees as $commande) {
            try {
                // annulerPourEchecPaiement() reverrouille et revérifie le
                // statut lui-même (voir CommandeAnnulationService) : même
                // si deux exécutions de cette commande se chevauchaient,
                // ou si la commande a été honorée entre le SELECT
                // ci-dessus et ce traitement, aucune double annulation
                // n'est possible.
                $this->commandeAnnulationService->annulerPourEchecPaiement($commande, null);
                $nbAnnulees++;
            } catch (CommandeAnnulationException $e) {
                // La commande a changé d'état entre-temps (payée,
                // annulée par un admin...) : rien d'anormal, on passe à
                // la suivante.
                continue;
            } catch (\Throwable $e) {
                $nbEchecs++;
                Log::error('Commandes.expiration_especes.echec', [
                    'idCommande' => $commande->idCommande,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->info("{$nbAnnulees} commande(s) espèces expirée(s) et annulée(s), stock restitué.");
        if ($nbEchecs > 0) {
            $this->warn("{$nbEchecs} échec(s) — voir storage/logs/laravel.log.");
        }

        return self::SUCCESS;
    }
}
