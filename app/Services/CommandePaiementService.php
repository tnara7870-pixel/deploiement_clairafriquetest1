<?php

namespace App\Services;

use App\Exceptions\CommandeAnnulationException;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommandePaiementService
{
    private const STATUTS_AUTORISES = ['en_attente', 'valide', 'echoue'];

    public function __construct(
        private CommandeAnnulationService $commandeAnnulationService,
        private CommandeStatutService $commandeStatutService,
    ) {}

    public function mettreAJourStatut(Paiement $paiement, string $statutPaiement, User $acteur): void
    {
        if (! in_array($statutPaiement, self::STATUTS_AUTORISES, true)) {
            throw new CommandeAnnulationException('Statut de paiement invalide.');
        }

        if ($paiement->commande?->statut === 'demande_annulation') {
            throw new CommandeAnnulationException(
                'Le paiement ne peut pas être modifié : une demande d\'annulation est en cours de traitement.'
            );
        }

        // Un paiement en ligne (wave / orange_money) ne doit JAMAIS être
        // basculé sur "valide" à la main : sa validation ne peut venir que
        // de la confirmation PayDunya elle-même (IPN ou retour, voir
        // PaiementDomainService::confirmerInvoice()), qui vérifie
        // réellement l'encaissement auprès du prestataire. Autoriser ce
        // formulaire à le faire aurait permis à n'importe quel compte
        // res.commande de valider gratuitement n'importe quelle commande
        // payée en ligne sans qu'un centime n'ait été réellement encaissé
        // — un simple bouton pour se livrer (ou livrer un tiers) des
        // marchandises gratuites. Seul le paiement "especes" (encaissé
        // physiquement par le personnel à la livraison/au retrait) peut
        // être validé manuellement ici. Voir audit du 05/08/2026, bug
        // critique — isolation des rôles / intégrité des paiements.
        if ($statutPaiement === 'valide' && $paiement->modePaiement !== 'especes') {
            throw new CommandeAnnulationException(
                'Un paiement en ligne (Wave / Orange Money) ne peut pas être validé manuellement : seule la confirmation PayDunya fait foi. Si ce paiement semble bloqué à tort, vérifiez son statut réel dans le tableau de bord PayDunya avant toute action.'
            );
        }

        DB::transaction(function () use ($paiement, $statutPaiement, $acteur) {
            $paiement = Paiement::findOrFail($paiement->idPaiement);
            $paiement->update([
                'statutPaiement' => $statutPaiement,
                'datePaiement' => $statutPaiement === 'valide' ? now() : $paiement->datePaiement,
            ]);

            $commande = $paiement->commande;
            if (! $commande) {
                return;
            }

            if ($statutPaiement === 'valide' && $commande->statut === 'en_attente') {
                $this->commandeStatutService->changerStatut($commande, 'validee', $acteur);
            }

            if ($statutPaiement === 'echoue' && $commande->statut === 'en_attente') {
                $this->commandeAnnulationService->annulerPourEchecPaiement($commande, $acteur);
            }
        });
    }
}
