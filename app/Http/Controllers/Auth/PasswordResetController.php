<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MotDePasseComplexe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * Réinitialisation de mot de passe en libre-service — absente du projet
 * jusqu'ici (aucune route, aucun contrôleur, aucune vue). Un client qui
 * oubliait son mot de passe était définitivement bloqué, sans recours.
 * Voir audit du 02/08/2026.
 *
 * S'appuie sur le PasswordBroker natif de Laravel (table
 * password_reset_tokens déjà présente en base, expiration/throttle déjà
 * configurés dans config/auth.php) plutôt que de réinventer la gestion
 * des tokens.
 */
class PasswordResetController extends Controller
{
    public function demande()
    {
        return view('auth.mot-de-passe-oublie');
    }

    public function envoyerLien(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $statut = Password::sendResetLink($request->only('email'));

        // Correction anomalie #2 (audit sécurité) : le cas "compte inexistant"
        // (INVALID_USER) doit produire exactement la même réponse que le cas
        // "lien envoyé" (même bandeau "success", même texte), sinon le simple
        // fait de recevoir un ->withErrors() permet à un attaquant de
        // distinguer les emails existants des emails inexistants et de
        // reconstituer une énumération de la base clients. Seuls les statuts
        // qui ne renseignent rien sur l'existence du compte (token invalide,
        // throttle, erreur) passent encore par messageStatut().
        if (in_array($statut, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            return back()->with('success', 'Un lien de réinitialisation a été envoyé à cette adresse, si elle est associée à un compte.');
        }

        return back()->withErrors(['email' => $this->messageStatut($statut)]);
    }

    public function formulaire(Request $request, string $token)
    {
        return view('auth.reinitialiser-mot-de-passe', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reinitialiser(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'motDePasse' => ['required', 'confirmed', new MotDePasseComplexe],
        ]);

        $statut = Password::reset(
            [
                'email' => $request->email,
                'token' => $request->token,
                'password' => $request->motDePasse,
                'password_confirmation' => $request->motDePasse_confirmation,
            ],
            function (User $user, string $motDePasse) {
                // forceFill() : motDePasse n'a pas vocation à être défini
                // via une mass-assignment générique, cette mise à jour
                // reste interne et contrôlée par le flux de réinitialisation.
                $user->forceFill(['motDePasse' => Hash::make($motDePasse)])->save();
            }
        );

        return $statut === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.')
            : back()->withErrors(['email' => $this->messageStatut($statut)]);
    }

    private function messageStatut(string $statut): string
    {
        return match ($statut) {
            Password::INVALID_USER => "Aucun compte n'est associé à cette adresse email.",
            Password::INVALID_TOKEN => 'Ce lien de réinitialisation est invalide ou a expiré.',
            Password::RESET_THROTTLED => 'Merci de patienter avant de redemander un nouveau lien.',
            default => 'Une erreur est survenue, réessayez.',
        };
    }
}
