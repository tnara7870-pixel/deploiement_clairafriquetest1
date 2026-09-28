<?php

namespace App\Http\Controllers\Commande;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Livraison;
use App\Models\Paiement;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $pointVente = Auth::user()->pointVenteAssigne;

        $enAttente = Commande::where('statut', 'en_attente')
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->count();

        $livraisonsAPreparer = Livraison::where('statutLivraison', 'preparee')
            ->when($pointVente, fn ($q) => $q->where('pointVenteAttribue', $pointVente))
            ->count();

        $livreesMois = Commande::where('statut', 'livree')
            ->whereMonth('dateCommande', now()->month)
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->count();

        $paiementsAVerifier = Paiement::where('statutPaiement', 'en_attente')
            ->when($pointVente, fn ($q) => $q->whereHas('commande.livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->count();

        // Demandes d'annulation en attente de traitement par l'admin/res.commande
        $demandesAnnulation = Commande::with(['utilisateur', 'paiement'])
            ->where('statut', 'demande_annulation')
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $dernieresCommandes = Commande::with(['utilisateur', 'paiement', 'livraison'])
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->latest('dateCommande')
            ->limit(5)
            ->get();

        // Commandes payées malgré une rupture de stock constatée à la
        // confirmation du paiement — nécessitent un traitement manuel.
        $commandesProblemeStock = Commande::with('utilisateur')
            ->where('problemeStock', true)
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->latest('dateCommande')
            ->limit(10)
            ->get();

        return view('commande.dashboard', compact(
            'enAttente',
            'livraisonsAPreparer',
            'livreesMois',
            'paiementsAVerifier',
            'demandesAnnulation',
            'commandesProblemeStock',
            'dernieresCommandes',
            'pointVente'
        ));
    }

    public function notificationsCount()
    {
        $pointVente = Auth::user()->pointVenteAssigne;

        $enAttente = Commande::where('statut', 'en_attente')
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente)))
            ->count();
        $paiements = Paiement::where('statutPaiement', 'en_attente')
            ->when($pointVente, fn ($q) => $q->whereHas('commande.livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente)))
            ->count();
        $livraisons = Livraison::where('statutLivraison', 'preparee')
            ->when($pointVente, fn ($q) => $q->where('pointVenteAttribue', $pointVente))
            ->count();
        $annulations = Commande::where('statut', 'demande_annulation')
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente)))
            ->count();
        $problemesStock = Commande::where('problemeStock', true)
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente)))
            ->count();

        return response()->json([
            'enAttente' => $enAttente,
            'paiements' => $paiements,
            'livraisons' => $livraisons,
            'annulations' => $annulations,
            'problemesStock' => $problemesStock,
            'total' => $enAttente + $paiements + $annulations + $problemesStock,
        ]);
    }
}
