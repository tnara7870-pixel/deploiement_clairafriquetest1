<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Politique de mot de passe : au moins 8 caractères, avec une majuscule,
 * une minuscule, un chiffre et un caractère spécial.
 *
 * Utilisée à l'inscription, au changement de mot de passe et à la
 * création d'un compte personnel par l'administrateur.
 */
class MotDePasseComplexe implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valeur = (string) $value;

        if (mb_strlen($valeur) < 8) {
            $fail('Le mot de passe doit contenir au moins 8 caractères.');

            return;
        }

        if (! preg_match('/[a-z]/', $valeur)) {
            $fail('Le mot de passe doit contenir au moins une lettre minuscule.');

            return;
        }

        if (! preg_match('/[A-Z]/', $valeur)) {
            $fail('Le mot de passe doit contenir au moins une lettre majuscule.');

            return;
        }

        if (! preg_match('/\d/', $valeur)) {
            $fail('Le mot de passe doit contenir au moins un chiffre.');

            return;
        }

        if (! preg_match('/[^a-zA-Z0-9]/', $valeur)) {
            $fail('Le mot de passe doit contenir au moins un caractère spécial (ex. ! ? @ # % &).');
        }
    }
}
