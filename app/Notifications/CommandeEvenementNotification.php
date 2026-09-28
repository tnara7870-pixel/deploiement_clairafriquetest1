<?php

namespace App\Notifications;

use App\Models\Commande;
use Illuminate\Notifications\Notification;

/**
 * Notification in-app générique pour les événements du workflow de
 * commande/annulation (demande reçue, décision, remboursement
 * enregistré...). N'implémente PAS ShouldQueue : l'envoi reste
 * synchrone, conformément au choix retenu pour cette fonctionnalité.
 *
 * Un seul type de notification paramétrable plutôt qu'une classe par
 * événement : évite la duplication pour un besoin qui reste, dans les
 * trois cas d'usage actuels, un simple message + lien vers la commande.
 */
class CommandeEvenementNotification extends Notification
{
    public function __construct(
        private readonly Commande $commande,
        private readonly string $titre,
        private readonly string $message,
        private readonly ?string $routeName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'idCommande' => $this->commande->idCommande,
            'numeroCommande' => $this->commande->numeroCommande,
            'titre' => $this->titre,
            'message' => $this->message,
            'routeName' => $this->routeName,
        ];
    }
}
