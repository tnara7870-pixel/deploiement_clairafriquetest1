<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Categorie;
use App\Models\SeuilAlerte;
use App\Services\ArticleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function __construct(private ArticleService $articleService) {}

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
            ->orderBy('designation')
            ->paginate(5)
            ->withQueryString();

        $categories = Categorie::where('statut', true)->get();
        $pointVente = Auth::user()->pointVenteAssigne;

        return view('stock.articles', compact('articles', 'categories', 'pointVente'));
    }

    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();
        $quantiteInitiale = (int) ($validated['quantiteStock'] ?? 0);
        $seuilMinimal = $validated['seuilMinimal'] ?? null;
        $pointVenteInitial = $validated['pointVenteInitial'] ?? null;
        $data = collect($validated)
            ->except(['image', 'seuilMinimal', 'quantiteStock', 'pointVenteInitial'])
            ->toArray();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $this->articleService->createArticle(
            $data,
            $quantiteInitiale,
            $seuilMinimal !== null ? (int) $seuilMinimal : null,
            $pointVenteInitial
        );

        return back()->with('success', 'Article créé avec succès.');
    }

    public function update(UpdateArticleRequest $request, int $id)
    {
        $article = Article::findOrFail($id);

        $data = $request->only(['designation', 'prix', 'idCategorie']);
        $data['statut'] = $request->has('statut') ? 1 : 0;

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $article->update($data);

        // Mettre à jour ou créer le seuil
        if ($request->filled('seuilMinimal')) {
            SeuilAlerte::updateOrCreate(
                ['idArticle' => $id],
                ['quantiteMinimale' => $request->seuilMinimal, 'estActif' => true]
            );
        }

        return back()->with('success', 'Article modifié avec succès.');
    }

    public function toggleStatut(int $id)
    {
        $article = Article::findOrFail($id);
        $article->update(['statut' => ! $article->statut]);

        return back()->with('success', 'Article '.($article->statut ? 'activé' : 'désactivé').'.');
    }

    public function destroy(int $id)
    {
        $article = Article::findOrFail($id);

        if ($article->ligneCommandes()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer "'.$article->designation.'" : il apparaît dans au moins une commande existante. Désactivez-le plutôt via son statut.'
            );
        }

        if ($article->quantiteStock > 0) {
            return back()->with(
                'error',
                'Impossible de supprimer "'.$article->designation.'" : il reste '.$article->quantiteStock.' unité(s) en stock. Videz le stock via un mouvement de sortie avant de le supprimer.'
            );
        }

        // Toute entrée initiale en stock crée déjà un MouvementStock, donc
        // quasiment tout article "réel" en a au moins un, même à
        // quantiteStock = 0. La FK mouvements_stock.idArticle est en
        // RESTRICT en base (voir migration) : sans ce contrôle explicite,
        // delete() remonte une QueryException brute non interceptée, donc
        // une 500 pour l'utilisateur au lieu d'un message clair.
        if ($article->mouvementsStock()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer "'.$article->designation.'" : il a un historique de mouvements de stock. Désactivez-le plutôt via son statut pour conserver la traçabilité.'
            );
        }

        if ($article->lignePaniers()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer "'.$article->designation.'" : il se trouve encore dans le panier d\'au moins un client.'
            );
        }

        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return back()->with('success', 'Article supprimé avec succès.');
    }
}
