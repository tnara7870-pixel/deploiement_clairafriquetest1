<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Categorie;
use App\Models\LignePanier; // Import indispensable !
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $categories = Categorie::where('statut', true)->get();

        // Article::visible() : l'article doit être actif ET sa catégorie
        // doit l'être aussi (voir Article::scopeVisible). Auparavant,
        // désactiver une catégorie n'avait aucun effet sur ses articles
        // ici. Voir audit du 02/08/2026.
        $query = Article::with(['categorie', 'seuilAlerte'])
            ->visible()
            ->when($request->categorie, fn ($q) => $q->where('idCategorie', $request->categorie))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('designation', 'like', '%'.$request->search.'%')
                        ->orWhere('reference', 'like', '%'.$request->search.'%')
                        ->orWhere('description', 'like', '%'.$request->search.'%');
                });
            });

        // Application du tri
        if ($request->tri === 'prix_asc') {
            $query->orderBy('prix', 'asc');
        } elseif ($request->tri === 'prix_desc') {
            $query->orderBy('prix', 'desc');
        } else {
            $query->latest();
        }

        $articles = $query->paginate(12);

        // Récupération des favoris
        $favorisIds = [];
        if (Auth::check()) {
            $favorisIds = Auth::user()->favoris()->pluck('idArticle')->toArray();
        }

        // Si la requête est AJAX (tri, filtre, pagination)
        if ($request->ajax()) {
            return response()->json([
                'html' => view('client.partials.articles-grille', compact('articles', 'favorisIds'))->render(),
                'pagination' => $articles->withQueryString()->links()->render(),
                'total' => $articles->total(),
            ]);
        }

        $nouveautes = Article::with('categorie')
            ->visible()
            ->latest()
            ->limit(4)
            ->get();

        return view('client.catalogue', compact(
            'categories', 'articles', 'nouveautes', 'favorisIds'
        ));
    }

    public function show(int $id)
    {
        $article = Article::with(['categorie', 'seuilAlerte'])
            ->visible()
            ->findOrFail($id);

        $similaires = Article::with('categorie')
            ->visible()
            ->where('idCategorie', $article->idCategorie)
            ->where('idArticle', '!=', $id)
            ->limit(6)
            ->get();

        $estFavori = false;
        $favorisIds = [];
        $quantiteAuPanier = 0;
        if (Auth::check()) {
            // $estFavori dérivé de $favorisIds au lieu d'une requête ->exists()
            // séparée : les deux interrogeaient la même table pour la même
            // information, un aller-retour DB en plus à chaque affichage de
            // fiche article. Voir audit du 05/08/2026.
            $favorisIds = Auth::user()->favoris()->pluck('idArticle')->toArray();
            $estFavori = in_array($id, $favorisIds);

            // Pré-remplit le sélecteur de quantité avec ce qui est déjà au
            // panier pour cet article, pour éviter la confusion "j'indique
            // 3 en pensant fixer le total, ça additionne à l'ancien".
            $quantiteAuPanier = LignePanier::whereHas('panier', fn ($q) => $q->where('idUtilisateur', Auth::user()->idUtilisateur)
            )->where('idArticle', $id)->value('quantite') ?? 0;
        }

        return view('client.article', compact(
            'article', 'similaires', 'estFavori', 'favorisIds', 'quantiteAuPanier'
        ));
    }

    // Méthode ajax de secours si tu utilises une route dédiée
    public function ajax(Request $request)
    {
        return $this->index($request);
    }
}
