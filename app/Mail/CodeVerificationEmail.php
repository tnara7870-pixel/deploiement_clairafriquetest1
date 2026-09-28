<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé à l'inscription (et lors d'un renvoi depuis la page de
 * vérification) contenant le code à 6 chiffres permettant de confirmer
 * que le client est bien propriétaire de l'adresse email renseignée.
 */
class CodeVerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
    ) {}

    public function build(): self
    {
        return $this->subject('Votre code de vérification ClaireAfrique')
            ->view('mail.code-verification')
            ->with([
                'user' => $this->user,
                'code' => $this->code,
            ]);
    }
}
