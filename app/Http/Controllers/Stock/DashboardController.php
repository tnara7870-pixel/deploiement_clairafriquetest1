<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\MouvementStock;
use App\Models\SeuilAlerte;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // NULL pour un compte non scopé (accès global, comportement
        // historique inchangé) ; sinon, tout ce qui suit est ramené au
        // stock de ce seul point de vente.
        $pointVente = Auth::user()->pointVenteAssigne;

        if ($pointVente) {
            $totalArticles = Article::where('statut', true)
                ->whereHas('stocksPointVente', fn ($q) => $q->where('pointVente', $pointVente))
                ->with(['stocksPointVente' => fn ($q) => $q->where('pointVente', $pointVente)])
                ->get()
                ->sum(fn ($a) => $a->stockPour($pointVente));

            $alertes = SeuilAlerte::with(['article.stocksPointVente'])
                ->where('estActif', true)
                ->get()
                ->filter(fn ($s) => $s->article->stockPour($pointVente) <= $s->quantiteMinimale)
                ->take(5);
        } else {
            $totalArticles = Article::where('statut', true)->sum('quantiteStock');

            $alertes = SeuilAlerte::with('article')
                ->where('estActif', true)
                ->get()
                ->filter(fn ($s) => $s->article->quantiteStock <= $s->quantiteMinimale)
                ->take(5);
        }

        $entreesJour = MouvementStock::where('typeMouvement', 'entree')
            ->whereDate('dateMouvement', today())
            ->when($pointVente, fn ($q) => $q->where('pointVente', $pointVente))
            ->sum('quantite');

        $sortiesJour = abs(MouvementStock::where('typeMouvement', 'sortie')
            ->whereDate('dateMouvement', today())
            ->when($pointVente, fn ($q) => $q->where('pointVente', $pointVente))
            ->sum('quantite'));

        $inventaire = Article::with(['categorie', 'seuilAlerte', 'stocksPointVente'])
            ->where('statut', true)
            ->orderBy('designation')
            ->paginate(5);

        // Distinct de $inventaire (paginé à 5, donc inutilisable comme
        // source du formulaire rapide "Enregistrer un mouvement" — il ne
        // proposerait que les articles de la page courante). Colonnes
        // volontairement réduites : ce champ ne sert qu'à peupler un
        // datalist de recherche, pas à afficher les articles eux-mêmes.
        $articlesPourRecherche = Article::where('statut', true)
            ->with('stocksPointVente')
            ->orderBy('designation')
            ->get(['idArticle', 'reference', 'designation', 'quantiteStock']);

        $derniersMouvements = MouvementStock::with(['article', 'utilisateur', 'commande'])
            ->when($pointVente, fn ($q) => $q->where('pointVente', $pointVente))
            ->latest('dateMouvement')
            ->limit(5)
            ->get();

        return view('stock.dashboard', compact(
            'totalArticles',
            'alertes',
            'entreesJour',
            'sortiesJour',
            'inventaire',
            'articlesPourRecherche',
            'derniersMouvements',
            'pointVente'
        ));
    }
}
