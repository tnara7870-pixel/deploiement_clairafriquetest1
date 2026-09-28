<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MotDePasseComplexe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CompteController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('client.compte', compact('user'));
    }

    public function update(Request $request)
    {
        // current_password : règle de validation native Laravel, qui
        // s'appuie sur User::getAuthPassword() (déjà câblé sur la colonne
        // motDePasse) — évite de dupliquer un Hash::check() manuel qui
        // existait à l'identique dans Shared\CompteController::update().
        // Voir audit du 04/08/2026.
        //
        // motDePasseActuel n'est ajouté aux règles QUE si l'utilisateur
        // change réellement de mot de passe (comme le faisait déjà l'ancien
        // code via son "if ($request->filled('motDePasse'))") : sinon, un
        // champ resté vide dans le formulaire bloquerait la mise à jour du
        // nom/prénom/téléphone à chaque fois, pour un mot de passe que le
        // client ne cherche pas à changer.
        $regles = [
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'telephone' => 'nullable|string|max:20',
            'motDePasse' => ['nullable', 'confirmed', new MotDePasseComplexe],
        ];

        if ($request->filled('motDePasse')) {
            $regles['motDePasseActuel'] = ['required', 'current_password'];
        }

        $request->validate($regles, [
            'motDePasseActuel.current_password' => 'Mot de passe actuel incorrect.',
        ]);

        $data = [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'telephone' => $request->telephone,
        ];

        if ($request->filled('motDePasse')) {
            $data['motDePasse'] = Hash::make($request->motDePasse);
        }

        User::where('idUtilisateur', Auth::user()->idUtilisateur)
            ->update($data);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
