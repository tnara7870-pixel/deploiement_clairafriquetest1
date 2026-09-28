<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Categorie;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['categorie', 'seuilAlerte', 'stocksPointVente'])
            ->when(request('search'), function ($q) {
                $q->where(function ($sub) {
                    $sub->where('designation', 'like', '%'.request('search').'%')
                        ->orWhere('reference', 'like', '%'.request('search').'%');
                });
            })
            ->when(request('categorie'), fn ($q) => $q->where('idCategorie', request('categorie'))
            )
            ->when(request('statut'), function ($q) {
                $q->where('statut', request('statut') === 'actif');
            })
            ->orderBy('designation')
            ->paginate(5)
            ->withQueryString();

        $categories = Categorie::where('statut', true)->get();

        return view('admin.articles', compact('articles', 'categories'));
    }
}
