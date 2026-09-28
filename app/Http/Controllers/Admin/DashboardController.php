<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Commande;
use App\Models\MouvementStock;
use App\Models\SeuilAlerte;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // KPIs
        $ventesJour = Commande::whereDate('dateCommande', today())
            ->where('statut', '!=', 'annulee')
            ->sum('montantTotal');

        $commandesMois = Commande::whereMonth('dateCommande', now()->month)
            ->whereYear('dateCommande', now()->year)
            ->count();

        $totalArticles = Article::where('statut', true)->sum('quantiteStock');

        $totalClients = User::role('client')->count();

        // Alertes stock
        $alertes = SeuilAlerte::with('article')
            ->where('estActif', true)
            ->get()
            ->filter(fn ($s) => $s->article->quantiteStock <= $s->quantiteMinimale);

        // Ventes par mois (6 derniers mois)
        $ventesMois = Commande::selectRaw('
                MONTH(dateCommande) as mois,
                YEAR(dateCommande) as annee,
                SUM(montantTotal) as total
            ')
            ->where('statut', '!=', 'annulee')
            ->where('dateCommande', '>=', now()->subMonths(6))
            ->groupBy('annee', 'mois')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        // Ventes par catégorie
        $ventesCategorie = DB::table('ligne_commandes')
            ->join('articles', 'ligne_commandes.idArticle', '=', 'articles.idArticle')
            ->join('categories', 'articles.idCategorie', '=', 'categories.idCategorie')
            ->join('commandes', 'ligne_commandes.idCommande', '=', 'commandes.idCommande')
            ->where('commandes.statut', '!=', 'annulee')
            ->selectRaw('categories.nomCategorie, SUM(ligne_commandes.quantite) as total')
            ->groupBy('categories.nomCategorie')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Top 5 articles les plus vendus (toutes commandes non annulées)
        $topArticles = DB::table('ligne_commandes')
            ->join('articles', 'ligne_commandes.idArticle', '=', 'articles.idArticle')
            ->join('commandes', 'ligne_commandes.idCommande', '=', 'commandes.idCommande')
            ->where('commandes.statut', '!=', 'annulee')
            ->selectRaw('
                articles.idArticle,
                articles.designation,
                articles.image,
                SUM(ligne_commandes.quantite) as quantiteVendue,
                SUM(ligne_commandes.quantite * ligne_commandes.prixUnitaire) as chiffreAffaires
            ')
            ->groupBy('articles.idArticle', 'articles.designation', 'articles.image')
            ->orderByDesc('quantiteVendue')
            ->limit(5)
            ->get();

        $rupturesImminentes = SeuilAlerte::with('article')
            ->where('estActif', true)
            ->get()
            ->filter(fn ($s) => $s->article
                && $s->article->statut
                && $s->article->quantiteStock <= $s->quantiteMinimale)
            ->sortBy(fn ($s) => $s->article->quantiteStock - $s->quantiteMinimale)
            ->take(5)
            ->values();

        // Dernières commandes
        $dernieresCommandes = Commande::with('utilisateur')
            ->latest('dateCommande')
            ->limit(5)
            ->get();

        // Derniers mouvements
        $derniersMouvements = MouvementStock::with('article', 'utilisateur')
            ->latest('dateMouvement')
            ->limit(5)
            ->get();

        // Comptes utilisateurs verrouillés
        $comptesVerrouilles = User::with('roles')
            ->whereNotNull('bloqueJusqua')
            ->where('bloqueJusqua', '>', now())
            ->orderBy('bloqueJusqua')
            ->limit(5)
            ->get();

        // Demandes d'annulation de commande en attente de décision
        $demandesAnnulation = Commande::with('utilisateur')
            ->where('statut', 'demande_annulation')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $commandesProblemeStock = Commande::with('utilisateur')
            ->where('problemeStock', true)
            ->latest('dateCommande')
            ->limit(5)
            ->get();

        $commandesARembourser = Commande::with('utilisateur', 'paiement')
            ->where('statut', 'annulee')
            ->whereHas('paiement', fn ($q) => $q->where('statutPaiement', 'valide'))
            ->whereDoesntHave('remboursements')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'ventesJour',
            'commandesMois',
            'totalArticles',
            'totalClients',
            'alertes',
            'ventesMois',
            'ventesCategorie',
            'topArticles',
            'rupturesImminentes',
            'comptesVerrouilles',
            'demandesAnnulation',
            'commandesProblemeStock',
            'dernieresCommandes',
            'derniersMouvements',
            'commandesARembourser'
        ));
    }
}
