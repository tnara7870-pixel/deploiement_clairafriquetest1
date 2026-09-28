<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Repositories\MouvementStockRepository;
use App\Services\MouvementStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MouvementController extends Controller
{
    public function index()
    {
        $repository = new MouvementStockRepository;
        $mouvements = $repository->paginate(array_merge(
            request()->only(['type', 'search', 'date_debut', 'date_fin', 'pointVente']),
            // Un compte scopé à un point ne voit et ne filtre que ses
            // propres mouvements ; un compte non scopé (accès global)
            // garde son filtre manuel (ou aucun filtre = tout voir).
            Auth::user()->estScopePointVente()
                ? ['pointVente' => Auth::user()->pointVenteAssigne]
                : []
        ));

        $articles = $repository->getActiveArticles();

        return view('stock.mouvements', compact('mouvements', 'articles'));
    }

    public function store(Request $request)
    {
        // Un compte scopé ne peut agir que sur SON point de vente, même si
        // le formulaire est manipulé pour en envoyer un autre : la valeur
        // envoyée par le client n'est jamais fiable pour ça.
        $pointVenteImpose = Auth::user()->pointVenteAssigne;

        $validated = $request->validate([
            'typeMouvement' => 'required|in:entree,sortie,retour,ajustement',
            'idArticle' => 'required|exists:articles,idArticle',
            'pointVente' => $pointVenteImpose ? 'nullable' : 'required|in:ucad,centre_ville',
            'quantite' => $request->typeMouvement === 'ajustement'
                ? 'required|integer|min:0'
                : 'required|integer|min:1',
            'motif' => 'nullable|string|max:150',
        ]);

        $validated['pointVente'] = $pointVenteImpose ?? $validated['pointVente'];

        try {
            $service = new MouvementStockService(new MouvementStockRepository);
            $article = $service->createMovement(Auth::user()->idUtilisateur, $validated);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $seuil = $article->fresh()->seuilAlerte;
        if ($seuil && $seuil->estActif && $article->fresh()->quantiteStock <= $seuil->quantiteMinimale) {
            return back()->with('warning', 'Mouvement enregistré. ⚠ Stock bas : '.$article->designation);
        }

        return back()->with('success', 'Mouvement enregistré avec succès.');
    }
}
