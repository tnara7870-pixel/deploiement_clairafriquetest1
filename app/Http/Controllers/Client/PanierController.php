<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\LignePanier;
use App\Models\Panier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanierController extends Controller
{
    public function index()
    {
        $panier = Panier::with('lignePaniers.article.stocksPointVente', 'lignePaniers.article.categorie')
            ->where('idUtilisateur', Auth::user()->idUtilisateur)
            ->first();

        $total = $panier ? $panier->calculerMontant() : 0;

        return view('client.panier', compact('panier', 'total'));
    }

    private function ajouterAuPanier(Request $request): array
    {
        $request->validate([
            'idArticle' => 'required|exists:articles,idArticle',
            'quantite' => 'required|integer|min:1',
        ]);

        $article = Article::findOrFail($request->idArticle);
        $panier = Panier::firstOrCreate(['idUtilisateur' => Auth::user()->idUtilisateur]);

        $ligne = LignePanier::where('idPanier', $panier->idPanier)
            ->where('idArticle', $request->idArticle)
            ->first();

        $nouvelleQte = $ligne ? $ligne->quantite + $request->quantite : $request->quantite;

        if ($nouvelleQte > $article->quantiteStock) {
            return [
                'success' => false,
                'message' => 'Stock insuffisant. Disponible au total sur les deux points de vente : '.$article->quantiteStock.' (voir la répartition par point dans votre panier).',
            ];
        }

        if ($ligne) {
            $ligne->update(['quantite' => $nouvelleQte]);
        } else {
            LignePanier::create([
                'idPanier' => $panier->idPanier,
                'idArticle' => $request->idArticle,
                'quantite' => $request->quantite,
            ]);
        }

        $panier->update(['dateModif' => now()]);

        return [
            'success' => true,
            'nbArticles' => $panier->lignePaniers()->count(),
        ];
    }

    public function ajouter(Request $request)
    {
        $resultat = $this->ajouterAuPanier($request);

        if (! $resultat['success']) {
            return back()->with('error', $resultat['message']);
        }

        return back()->with('success', 'Article ajouté au panier.');
    }

    public function ajouterAjax(Request $request)
    {
        $resultat = $this->ajouterAuPanier($request);

        if (! $resultat['success']) {
            return response()->json($resultat);
        }

        return response()->json([
            'success' => true,
            'message' => 'Article ajouté au panier',
            'nbArticles' => $resultat['nbArticles'],
        ]);
    }

    public function definirQuantiteAjax(Request $request)
    {
        $request->validate([
            'idArticle' => 'required|exists:articles,idArticle',
            'quantite' => 'required|integer|min:1',
        ]);

        $article = Article::findOrFail($request->idArticle);

        if ($request->quantite > $article->quantiteStock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant. Disponible au total sur les deux points de vente : '.$article->quantiteStock.' (voir la répartition par point dans votre panier).',
            ]);
        }

        $panier = Panier::firstOrCreate(
            ['idUtilisateur' => Auth::user()->idUtilisateur]
        );

        LignePanier::updateOrCreate(
            ['idPanier' => $panier->idPanier, 'idArticle' => $request->idArticle],
            ['quantite' => $request->quantite]
        );

        $panier->update(['dateModif' => now()]);

        $nbArticles = $panier->lignePaniers()->count();

        return response()->json([
            'success' => true,
            'message' => 'Panier mis à jour',
            'nbArticles' => $nbArticles,
        ]);
    }

    private function modifierLigne(Request $request, int $id): array
    {
        $request->validate(['quantite' => 'required|integer|min:1']);

        // Vérifier que la ligne appartient au panier de l'utilisateur connecté
        $ligne = LignePanier::whereHas('panier', function ($q) {
            $q->where('idUtilisateur', Auth::user()->idUtilisateur);
        })->findOrFail($id);

        $article = $ligne->article;

        if ($request->quantite > $article->quantiteStock) {
            return [
                'success' => false,
                'message' => 'Stock insuffisant. Disponible au total sur les deux points de vente : '.$article->quantiteStock.' (voir la répartition par point dans votre panier).',
                'ligne' => $ligne,
            ];
        }

        $ligne->update(['quantite' => $request->quantite]);

        return ['success' => true, 'ligne' => $ligne, 'article' => $article];
    }

    public function modifier(Request $request, int $id)
    {
        $resultat = $this->modifierLigne($request, $id);

        if (! $resultat['success']) {
            return back()->with('error', $resultat['message']);
        }

        return back()->with('success', 'Quantité mise à jour.');
    }

    public function modifierAjax(Request $request, int $id)
    {
        $resultat = $this->modifierLigne($request, $id);

        if (! $resultat['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultat['message'],
                'quantite' => $resultat['ligne']->quantite,
            ]);
        }

        $ligne = $resultat['ligne'];
        $article = $resultat['article'];
        $sousTotal = $ligne->quantite * $article->prix;
        $panier = $ligne->panier->load('lignePaniers.article');
        $total = $panier->calculerMontant();

        return response()->json([
            'success' => true,
            'message' => 'Quantité mise à jour',
            'sousTotal' => number_format($sousTotal, 0, ',', ' ').' F',
            'total' => number_format($total, 0, ',', ' ').' F',
        ]);
    }

    public function supprimer(int $id)
    {
        $ligne = LignePanier::whereHas('panier', function ($q) {
            $q->where('idUtilisateur', Auth::user()->idUtilisateur);
        })->findOrFail($id);

        $ligne->delete();

        return back()->with('success', 'Article retiré du panier.');
    }
}
