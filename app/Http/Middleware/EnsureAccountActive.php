<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware pour déconnecter immédiatement un utilisateur dont le
 * compte a été désactivé/bloqué par l'administrateur (effet immédiat
 * sur les sessions actives). Utilisé pour résoudre J5.
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Si le compte est désactivé, forcer la déconnexion et invalider
            // la session courante.
            if (! $user->statut) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Votre compte a été désactivé par un administrateur. Vous avez été déconnecté. Contactez le support si vous pensez qu\'il s\'agit d\'une erreur.');
            }
        }

        return $next($request);
    }
}
