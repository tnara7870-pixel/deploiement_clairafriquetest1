<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Remplace l'email générique par défaut de Laravel
 * (Illuminate\Auth\Notifications\ResetPassword) par une version
 * personnalisée aux couleurs de ClaireAfrique, avec le prénom du client.
 * Déclenché via User::sendPasswordResetNotification().
 */
class ReinitialisationMotDePasseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $urlReinitialisation,
    ) {}

    public function build(): self
    {
        return $this->subject('Réinitialisation de votre mot de passe ClaireAfrique')
            ->view('mail.reinitialisation-mot-de-passe')
            ->with([
                'user' => $this->user,
                'url' => $this->urlReinitialisation,
            ]);
    }
}
