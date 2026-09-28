<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Categorie;

class HomeController extends Controller
{
    public function index()
    {
        // Nouveautés (4 derniers articles)
        $nouveautes = Article::with('categorie')
            ->where('statut', true)
            ->latest()
            ->limit(4)
            ->get();

        // Catégorie littérature africaine
        $categorielit = Categorie::where('nomCategorie', 'like', '%littér%')
            ->orWhere('nomCategorie', 'like', '%litt%')
            ->first();

        $litteratureAfricaine = collect();
        if ($categorielit) {
            $litteratureAfricaine = Article::with('categorie')
                ->where('statut', true)
                ->where('idCategorie', $categorielit->idCategorie)
                ->limit(6)
                ->get();
        }

        // Toutes les catégories actives ayant au moins un article actif
        $categories = Categorie::where('statut', true)
            ->whereHas('articles', fn ($q) => $q->where('statut', true))
            ->withCount(['articles' => fn ($q) => $q->where('statut', true)])
            ->get();

        // Stats pour la bannière
        $totalArticles = Article::where('statut', true)->count();
        $totalCategories = Categorie::where('statut', true)->count();

        return view('home', compact(
            'nouveautes',
            'litteratureAfricaine',
            'categories',
            'categorielit',
            'totalArticles',
            'totalCategories'
        ));
    }
}
