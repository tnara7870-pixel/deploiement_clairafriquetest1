<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Favori;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $dernieresCommandes = Commande::with(['livraison', 'paiement'])
            ->where('idUtilisateur', $user->idUtilisateur)
            ->latest('dateCommande')
            ->limit(3)
            ->get();

        $favoris = Favori::with('article.categorie')
            ->where('idUtilisateur', $user->idUtilisateur)
            ->latest()
            ->limit(4)
            ->get();

        $totalCommandes = Commande::where('idUtilisateur', $user->idUtilisateur)->count();
        $totalDepense = Commande::where('idUtilisateur', $user->idUtilisateur)
            ->where('statut', '!=', 'annulee')
            ->sum('montantTotal');
        $commandesEnCours = Commande::where('idUtilisateur', $user->idUtilisateur)
            ->whereIn('statut', ['validee', 'en_livraison'])
            ->count();

        return view('client.dashboard', compact(
            'user', 'dernieresCommandes', 'favoris',
            'totalCommandes', 'totalDepense', 'commandesEnCours'
        ));
    }
}
