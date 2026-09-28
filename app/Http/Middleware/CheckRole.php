<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Connectez-vous pour accéder à cette page.');
        }

        // Spatie/Permission s'appuie sur $this->getKey(), qui respecte déjà
        // notre clé primaire personnalisée (idUtilisateur) : pas besoin de
        // requête SQL brute sur model_has_roles pour ça.
        if (Auth::user()->hasAnyRole($roles)) {
            // Vérifier que le compte est actif (J5). Si le statut est faux,
            // forcer la déconnexion — sécurité supplémentaire au cas où
            // EnsureAccountActive n'aurait pas encore été exécuté.
            if (! Auth::user()->statut) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Votre compte a été désactivé par un administrateur. Vous avez été déconnecté. Contactez le support si vous pensez qu\'il s\'agit d\'une erreur.');
            }

            return $next($request);
        }

        abort(403, 'Accès non autorisé.');
    }
}
