<?php

namespace App\Services;

use App\Exceptions\CommandeAnnulationException;
use App\Models\Commande;
use App\Models\HistoriqueCommande;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Progression logistique "normale" d'une commande :
 * en_attente → validee → en_livraison → livree.
 *
 * L'annulation (demande, validation, refus) et le remboursement sont
 * gérés exclusivement par CommandeAnnulationService, qui a ses propres
 * règles d'éligibilité et acteurs (seul l'administrateur décide). Ce
 * service-ci refuse de faire transiter une commande vers "annulee" ou
 * "demande_annulation", refuse toute action tant qu'une demande
 * d'annulation est en cours, ET refuse toute régression dans le temps
 * (ex. repasser une commande "livree" à "en_attente") — voir audit du
 * 02/08/2026, bug critique 2 : vérifier seulement l'appartenance à la
 * liste des statuts autorisés ne suffit pas, il faut vérifier le sens
 * de la transition.
 */
class CommandeStatutService
{
    private const ORDRE = [
        'en_attente' => 0,
        'validee' => 1,
        'en_livraison' => 2,
        'livree' => 3,
    ];

    public function changerStatut(Commande $commande, string $nouveauStatut, ?User $acteur = null): void
    {
        if (! array_key_exists($nouveauStatut, self::ORDRE)) {
            throw new CommandeAnnulationException(
                "Ce statut ne peut pas être appliqué ici. L'annulation se fait via le workflow dédié."
            );
        }

        DB::transaction(function () use ($commande, $nouveauStatut, $acteur) {
            // Verrouille la ligne pour la durée de la transaction : évite
            // les changements de statut concurrents incohérents.
            $commande = Commande::lockForUpdate()->findOrFail($commande->idCommande);

            if (in_array($commande->statut, ['demande_annulation', 'annulee'], true)) {
                throw new CommandeAnnulationException(
                    'Impossible de modifier le statut logistique : une demande d\'annulation est en cours ou la commande est déjà annulée.'
                );
            }

            $rangActuel = self::ORDRE[$commande->statut] ?? null;
            $rangCible = self::ORDRE[$nouveauStatut];

            if ($nouveauStatut === 'en_livraison' && $commande->livraison?->modeLivraison === 'boutique') {
                throw new CommandeAnnulationException(
                    'Le statut "En livraison" ne s\'applique pas à un retrait en boutique.'
                );
            }

            if ($rangActuel === null) {
                throw new CommandeAnnulationException(
                    "Impossible de revenir en arrière : la commande est déjà au statut '{$commande->statut}'."
                );
            }

            // Rejouer le même statut (ex. LivraisonService qui a déjà
            // positionné la livraison sur ce palier) est un no-op
            // silencieux, pas une erreur — voir refonte du 04/08/2026,
            // qui corrige le blocage causé par une exception ici.
            if ($rangCible === $rangActuel) {
                return;
            }

            if ($rangCible < $rangActuel) {
                throw new CommandeAnnulationException(
                    "Impossible de revenir en arrière : la commande est déjà au statut '{$commande->statut}'."
                );
            }

            $statutPrecedent = $commande->statut;
            $commande->update(['statut' => $nouveauStatut]);

            // Faire progresser une commande au-delà de "en_attente" signifie,
            // dans les faits, que le paiement est acté (espèces encaissées à
            // la validation, ou paiement en ligne déjà confirmé mais dont le
            // statut n'aurait pas suivi). On ne rétrograde jamais un paiement
            // ici — seulement "en_attente" → "valide", jamais l'inverse, et
            // jamais un paiement "remboursé" ou "échoué" (hors périmètre de
            // ce service, voir CommandeAnnulationService).
            if ($commande->paiement && $commande->paiement->statutPaiement === 'en_attente') {
                $commande->paiement->update(['statutPaiement' => 'valide']);
            }

            if ($nouveauStatut === 'en_livraison') {
                $commande->livraison?->update(['statutLivraison' => 'en_livraison']);
            } elseif ($nouveauStatut === 'livree') {
                $commande->livraison?->update([
                    'statutLivraison' => 'livree',
                    'dateLivraison' => now(),
                ]);
            }

            HistoriqueCommande::create([
                'idCommande' => $commande->idCommande,
                'statutPrecedent' => $statutPrecedent,
                'statutNouveau' => $nouveauStatut,
                'action' => 'changement_statut',
                'idUtilisateur' => $acteur?->idUtilisateur,
                'commentaire' => null,
                'dateAction' => now(),
            ]);
        });
    }
}
