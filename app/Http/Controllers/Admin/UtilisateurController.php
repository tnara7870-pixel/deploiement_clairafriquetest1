<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MotDePasseComplexe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UtilisateurController extends Controller
{
    public function index()
    {
        $utilisateurs = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'client');
            })
            ->when(request('search'), function ($q) {
                $terme = request('search');
                $q->where(function ($sub) use ($terme) {
                    $sub->where('nom', 'like', "%{$terme}%")
                        ->orWhere('prenom', 'like', "%{$terme}%")
                        ->orWhere('email', 'like', "%{$terme}%");
                });
            })
            ->when(request('role'), function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->where('name', request('role'));
                });
            })
            ->orderBy('dateCreation', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('admin.utilisateurs', compact('utilisateurs'));
    }

    public function bloquer(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('administrateur')) {
            return back()->with('error',
                'Impossible de bloquer un compte administrateur.'
            );
        }

        $user->update(['statut' => ! $user->statut]);
        $msg = $user->statut ? 'Compte activé.' : 'Compte bloqué.';

        // Si on vient de bloquer le compte, supprimer les sessions actives
        // associées à cet utilisateur (J5) — tentative pragmatique : on
        // parcourt la table sessions et supprime toute session dont la
        // payload contient l'id de l'utilisateur. C'est robuste tant que
        // la session n'est pas chiffrée.
        if (! $user->statut) {
            try {
                $table = config('session.table', 'sessions');
                $sessions = DB::table($table)->get();
                foreach ($sessions as $s) {
                    $payload = $s->payload ?? '';
                    if ($payload && strpos(base64_decode($payload), (string) $user->idUtilisateur) !== false) {
                        DB::table($table)->where('id', $s->id)->delete();
                    }
                }
            } catch (\Throwable $e) {
                // Ne pas empêcher l'action principale si la suppression des
                // sessions échoue ; logguer pour diagnostic.
                Log::warning('Impossible de purger sessions pour utilisateur bloqué', ['idUtilisateur' => $user->idUtilisateur, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('success', $msg);
    }

    public function debloquer(int $id)
    {
        $user = User::findOrFail($id);

        // forceFill() : ces deux colonnes sont volontairement hors de
        // $fillable (voir User::$fillable), cette action reste interne et
        // contrôlée par l'administrateur, pas par une entrée utilisateur.
        $user->forceFill([
            'tentativesEchouees' => 0,
            'bloqueJusqua' => null,
        ])->save();

        return back()->with('success',
            'Le compte de '.$user->prenom.' '.$user->nom.' a été débloqué.'
        );
    }

    public function updateRole(Request $request, int $id)
    {
        $request->validate([
            'role' => 'required|in:client,res.stock,res.commande,administrateur',
            // Requis uniquement pour res.stock/res.commande (validé plus
            // bas) : un client ou un administrateur n'a pas de point de
            // vente assigné, l'accès reste global par nature.
            'pointVenteAssigne' => 'nullable|in:ucad,centre_ville',
        ]);

        $user = User::findOrFail($id);

        // Empêcher de rétrograder le dernier admin
        if ($user->roles->first()?->name === 'administrateur' &&
            $request->role !== 'administrateur') {
            $nbAdmins = User::whereHas('roles', fn ($q) => $q->where('name', 'administrateur')
            )->count();

            if ($nbAdmins <= 1) {
                return back()->with('error',
                    'Impossible de rétrograder le dernier administrateur.'
                );
            }
        }

        $user->syncRoles([$request->role]);

        // Un compte res.stock/res.commande peut être scopé à un point de
        // vente (pointVenteAssigne renseigné) ou rester à accès global
        // (laissé à null) ; tout autre rôle n'a pas cette notion.
        $user->update([
            'pointVenteAssigne' => in_array($request->role, ['res.stock', 'res.commande'], true)
                ? $request->pointVenteAssigne
                : null,
        ]);

        $roleLabels = [
            'client' => 'Client',
            'res.stock' => 'Responsable stock',
            'res.commande' => 'Responsable commande',
            'administrateur' => 'Administrateur',
        ];
        $message = $roleLabels[$request->role].' assigné à '.$user->prenom.' '.$user->nom.'.';
        if (in_array($request->role, ['res.stock', 'res.commande'], true)) {
            $message .= $request->pointVenteAssigne
                ? ' Point de vente : '.($request->pointVenteAssigne === 'ucad' ? 'UCAD' : 'Centre-ville').'.'
                : ' Accès global (aucun point de vente assigné).';
        }

        return back()->with('success', $message);
    }

    public function updatePassword(Request $request, int $id)
    {
        $request->validate([
            'motDePasse' => ['required', 'confirmed', new MotDePasseComplexe],
        ]);

        $user = User::findOrFail($id);

        if (! $user->hasAnyRole(['res.stock', 'res.commande'])) {
            return back()->with('error', 'Le mot de passe ne peut être modifié que pour un responsable de stock ou de commande.');
        }

        $user->update(['motDePasse' => Hash::make($request->motDePasse)]);

        $role = $user->roles->first()?->name;
        $roleLabel = $role === 'res.stock' ? 'responsable stock' : ($role === 'res.commande' ? 'responsable commande' : 'responsable');

        return back()->with('success', 'Mot de passe de '.$user->prenom.' '.$user->nom.' ('.$roleLabel.') réinitialisé avec succès.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:utilisateurs,email',
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|in:client,res.stock,res.commande,administrateur',
            'pointVenteAssigne' => 'nullable|in:ucad,centre_ville',
            'motDePasse' => ['required', new MotDePasseComplexe],
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'motDePasse' => Hash::make($request->motDePasse),
            'telephone' => $request->telephone,
            'statut' => true,
            'pointVenteAssigne' => in_array($request->role, ['res.stock', 'res.commande'], true)
                ? $request->pointVenteAssigne
                : null,
        ]);

        $user->syncRoles([$request->role]);

        return back()->with('success', 'Utilisateur créé avec succès.');
    }
}
