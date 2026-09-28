<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\CodeVerificationEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Vérification de l'adresse email à l'inscription, par code à 6 chiffres
 * envoyé par email (voir migration add_email_verification_to_utilisateurs).
 *
 * Le code est stocké hashé (comme motDePasse) et expire après 15 minutes ;
 * un renvoi régénère un nouveau code et invalide l'ancien.
 */
class EmailVerificationController extends Controller
{
    private const DUREE_VALIDITE_MINUTES = 15;

    // Affiche la page de saisie du code
    public function formulaire()
    {
        $user = Auth::user();

        if ($user->emailVerifieLe !== null) {
            return redirect()->route('client.catalogue');
        }

        return view('auth.verification-email');
    }

    // Vérifie le code saisi
    public function verifier(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Veuillez saisir le code reçu par email.',
        ]);

        $user = Auth::user();

        if ($user->emailVerifieLe !== null) {
            return redirect()->route('client.catalogue');
        }

        if (
            ! $user->codeVerification
            || ! $user->codeVerificationExpire
            || $user->codeVerificationExpire->isPast()
        ) {
            return back()->withErrors([
                'code' => 'Ce code a expiré. Demandez-en un nouveau ci-dessous.',
            ]);
        }

        if (! Hash::check($request->code, $user->codeVerification)) {
            return back()->withErrors([
                'code' => 'Code incorrect. Veuillez réessayer.',
            ]);
        }

        // forceFill() : ces colonnes ne sont pas dans $fillable (même
        // logique que tentativesEchouees/bloqueJusqua), cette mise à jour
        // reste interne et contrôlée par ce contrôleur uniquement.
        $user->forceFill([
            'emailVerifieLe' => now(),
            'codeVerification' => null,
            'codeVerificationExpire' => null,
        ])->save();

        return redirect()->route('client.catalogue')
            ->with('success', 'Adresse email vérifiée. Bienvenue sur ClaireAfrique !');
    }

    // Renvoie un nouveau code (invalide l'ancien)
    public function renvoyer(Request $request)
    {
        $user = Auth::user();

        if ($user->emailVerifieLe !== null) {
            return redirect()->route('client.catalogue');
        }

        $this->envoyerCode($user);

        return back()->with('success', 'Un nouveau code vient de vous être envoyé par email.');
    }

    // Génère un code à 6 chiffres, le stocke hashé avec expiration, et l'envoie par email.
    public static function envoyerCode(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'codeVerification' => Hash::make($code),
            'codeVerificationExpire' => now()->addMinutes(self::DUREE_VALIDITE_MINUTES),
        ])->save();

        Mail::to($user->email)->send(new CodeVerificationEmail($user, $code));
    }
}
