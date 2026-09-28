<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Empêche un client dont l'email n'est pas encore vérifié d'accéder à
 * l'espace catalogue/commande en saisissant directement une URL (le seul
 * blocage à la redirection post-inscription ne suffirait pas).
 *
 * Ne s'applique qu'aux comptes 'client' : les rôles internes
 * (administrateur, res.stock, res.commande) sont créés par un
 * administrateur et n'ont pas ce flux d'inscription.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->hasRole('client') && $user->emailVerifieLe === null) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
