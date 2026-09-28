<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::withCount('articles')
            ->when(request('search'), fn ($q) => $q->where('nomCategorie', 'like', '%'.request('search').'%')
            )
            ->paginate(5)
            ->withQueryString();

        return view('stock.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomCategorie' => 'required|unique:categories,nomCategorie|max:100',
            'description' => 'nullable|string',
        ]);

        Categorie::create([
            'nomCategorie' => $request->nomCategorie,
            'description' => $request->description,
            'statut' => true,
        ]);

        return back()->with('success', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $categorie = Categorie::findOrFail($id);

        $request->validate([
            'nomCategorie' => 'required|max:100|unique:categories,nomCategorie,'.$id.',idCategorie',
            'description' => 'nullable|string',
        ]);

        $categorie->update([
            'nomCategorie' => $request->nomCategorie,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Catégorie modifiée.');
    }

    public function toggleStatut(int $id)
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->update(['statut' => ! $categorie->statut]);

        return back()->with('success', 'Catégorie '.($categorie->statut ? 'activée' : 'désactivée').'.');
    }

    public function destroy(int $id)
    {
        $categorie = Categorie::withCount('articles')->findOrFail($id);

        if ($categorie->articles_count > 0) {
            return back()->with(
                'error',
                'Impossible de supprimer "'.$categorie->nomCategorie.'" : elle contient encore '.
                $categorie->articles_count.' article(s). Déplacez-les vers une autre catégorie ou supprimez-les d\'abord.'
            );
        }

        $categorie->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
