<?php

namespace App\Services;

use App\Exceptions\CommandeAnnulationException;
use App\Repositories\LivraisonRepository;
use Illuminate\Support\Facades\DB;

class LivraisonService
{
    /**
     * Refonte du 04/08/2026 : 3 statuts actifs au lieu de 4
     * ('expediee' et 'en_cours' fusionnés en 'en_livraison'). Ce statut
     * porte désormais exactement le même nom que le statut de commande
     * qu'il déclenche (voir mettreAJourStatut() ci-dessous) : la
     * correspondance est donc directe, il ne peut plus y avoir deux
     * statuts de livraison distincts qui retentent tous les deux de faire
     * transiter la commande vers la même cible — c'était précisément la
     * cause du bug critique qui bloquait le passage "expédiée" → "en
     * cours" (voir audit du 02/08/2026, point critique 2).
     */
    private const ORDRE_LIVRAISON = [
        'preparee' => 0,
        'en_livraison' => 1,
        'livree' => 2,
    ];

    public function __construct(
        private LivraisonRepository $livraisonRepository,
        private CommandeStatutService $commandeStatutService,
    ) {}

    public function mettreAJourStatut(int $id, string $statutLivraison): void
    {
        if (! array_key_exists($statutLivraison, self::ORDRE_LIVRAISON)) {
            throw new CommandeAnnulationException('Statut de livraison invalide.');
        }

        DB::transaction(function () use ($id, $statutLivraison) {
            $livraison = $this->livraisonRepository->findByIdForUpdate($id);
            if (! $livraison) {
                throw new CommandeAnnulationException('Livraison introuvable.');
            }

            if ($livraison->statutLivraison === 'annulee') {
                throw new CommandeAnnulationException(
                    'Cette livraison est annulée, son statut ne peut plus être modifié ici.'
                );
            }

            if ($livraison->commande?->statut === 'demande_annulation') {
                throw new CommandeAnnulationException(
                    'La livraison ne peut pas être modifiée : une demande d\'annulation est en cours de traitement.'
                );
            }

            if ($statutLivraison === 'en_livraison' && $livraison->modeLivraison === 'boutique') {
                throw new CommandeAnnulationException(
                    'Le statut "En livraison" ne s\'applique pas à un retrait en boutique.'
                );
            }

            $rangActuel = self::ORDRE_LIVRAISON[$livraison->statutLivraison] ?? null;
            $rangCible = self::ORDRE_LIVRAISON[$statutLivraison];

            // Rejouer le même statut (double-clic, double soumission de
            // formulaire) est un no-op silencieux, pas une erreur : une
            // action idempotente ne doit jamais échouer côté utilisateur.
            if ($rangActuel !== null && $rangCible === $rangActuel) {
                return;
            }

            if ($rangActuel !== null && $rangCible < $rangActuel) {
                throw new CommandeAnnulationException(
                    "Impossible de revenir en arrière : la livraison est déjà au statut '{$livraison->statutLivraison}'."
                );
            }

            $livraison->update(['statutLivraison' => $statutLivraison]);
            if ($statutLivraison === 'livree') {
                $livraison->update(['dateLivraison' => now()]);
            }

            // Le statut de livraison ET le statut de commande partagent
            // désormais le même vocabulaire pour les étapes "en_livraison"
            // et "livree" : correspondance directe, sans table de mapping.
            if (in_array($statutLivraison, ['en_livraison', 'livree'], true)) {
                $this->commandeStatutService->changerStatut(
                    $livraison->commande,
                    $statutLivraison,
                    null
                );
            }
        });
    }
}
