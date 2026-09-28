<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MotDePasseComplexe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Espace "Mon compte" mutualisé pour le personnel (administrateur,
 * responsable stock, responsable commande). Contrairement à l'espace
 * client, seul le changement de mot de passe est proposé ici : les
 * informations personnelles du personnel (nom, prénom, téléphone) sont
 * gérées par l'administrateur depuis la gestion des utilisateurs.
 *
 * Le même contrôleur dessert les trois espaces ; la vue et la route de
 * redirection sont déduites du préfixe du nom de la route courante
 * (admin.compte, stock.compte, commande.compte), ce qui évite de
 * dupliquer ce contrôleur trois fois.
 */
class CompteController extends Controller
{
    public function index(Request $request)
    {
        $espace = $this->espaceCourant($request);
        $data = [
            'user' => Auth::user(),
        ];

        if (Auth::user()->hasRole('administrateur')) {
            $data['responsables'] = User::with('roles')
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['res.stock', 'res.commande']);
                })
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }

        return view($espace.'.compte', $data);
    }

    public function update(Request $request): RedirectResponse
    {
        $espace = $this->espaceCourant($request);

        // current_password : voir Client\CompteController::update() pour
        // le détail — même correctif, ici sans la subtilité du champ
        // optionnel puisque ce formulaire ne sert qu'au changement de
        // mot de passe (toujours requis).
        if (! Auth::user()->hasRole('administrateur')) {
            abort(403, 'Accès non autorisé. La modification de mot de passe doit être faite par l\'administrateur.');
        }

        $request->validate([
            'motDePasseActuel' => ['required', 'current_password'],
            'motDePasse' => ['required', 'confirmed', new MotDePasseComplexe],
        ], [
            'motDePasseActuel.current_password' => 'Mot de passe actuel incorrect.',
        ]);

        User::where('idUtilisateur', Auth::user()->idUtilisateur)
            ->update(['motDePasse' => Hash::make($request->motDePasse)]);

        return redirect()
            ->route($espace.'.compte')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Déduit l'espace (admin, stock ou commande) à partir du nom de la
     * route courante, ex. "admin.compte" -> "admin".
     */
    private function espaceCourant(Request $request): string
    {
        $nomRoute = $request->route()->getName();

        return explode('.', $nomRoute)[0];
    }
}
