<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SeuilAlerte;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AlerteController extends Controller
{
    private const PAR_PAGE = 5;

    public function index()
    {
        // NULL pour un compte non scopé : comportement historique inchangé
        // (comparaison sur le total global). Sinon, comparaison sur le
        // stock du seul point assigné — le seuil d'alerte, lui, reste un
        // réglage unique par article, pas dupliqué par point.
        $pointVente = Auth::user()->pointVenteAssigne;

        $quantitePour = fn ($article) => $pointVente ? $article->stockPour($pointVente) : $article->quantiteStock;

        // $alertes et $tousArticles sont construits via filter()/sortBy() sur
        // des Collections déjà chargées (le volume — quelques dizaines
        // d'articles au plus — reste largement gérable en mémoire) : on ne
        // peut donc pas utiliser ->paginate() côté requête SQL directement,
        // on pagine la Collection déjà filtrée à la main. Deux listes
        // indépendantes sur la même page → deux noms de page distincts,
        // sinon elles se marcheraient dessus dans l'URL.
        $alertes = SeuilAlerte::with('article.categorie', 'article.stocksPointVente')
            ->where('estActif', true)
            ->get()
            ->filter(fn ($s) => $quantitePour($s->article) <= $s->quantiteMinimale)
            ->sortBy(fn ($s) => $quantitePour($s->article))
            ->values();

        $alertes = $this->paginerCollection($alertes, 'page_alertes');

        $tousArticles = Article::with('seuilAlerte', 'stocksPointVente')
            ->where('statut', true)
            ->orderBy('designation')
            ->get();

        $seuilsDefinis = $this->paginerCollection(
            $tousArticles->filter(fn ($a) => $a->seuilAlerte)->values(),
            'page_seuils'
        );

        return view('stock.alertes', compact('alertes', 'tousArticles', 'seuilsDefinis', 'pointVente'));
    }

    private function paginerCollection($collection, string $pageName): LengthAwarePaginator
    {
        $page = (int) request($pageName, 1);

        return new LengthAwarePaginator(
            $collection->forPage($page, self::PAR_PAGE)->values(),
            $collection->count(),
            self::PAR_PAGE,
            $page,
            ['path' => request()->url(), 'pageName' => $pageName, 'query' => request()->query()]
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'idArticle' => 'required|exists:articles,idArticle',
            'quantiteMinimale' => 'required|integer|min:0',
        ]);

        SeuilAlerte::updateOrCreate(
            ['idArticle' => $request->idArticle],
            ['quantiteMinimale' => $request->quantiteMinimale, 'estActif' => true]
        );

        return back()->with('success', 'Seuil d\'alerte défini.');
    }

    public function toggle(int $id)
    {
        $seuil = SeuilAlerte::findOrFail($id);
        $seuil->update(['estActif' => ! $seuil->estActif]);

        return back()->with('success', 'Seuil mis à jour.');
    }
}
