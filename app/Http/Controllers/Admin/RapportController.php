<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RapportExport;
use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;

        // Récupération des données centralisées (avec limite à 10 pour l'affichage)
        $data = $this->getDonneesRapport($dateDebut, $dateFin, $limitTop = 10);

        return view('admin.rapports', array_merge($data, [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ]));
    }

    public function export(Request $request)
    {
        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;

        // Récupération des données globales (sans limite pour l'export Excel)
        $data = $this->getDonneesRapport($dateDebut, $dateFin, $limitTop = null);

        $nomFichier = 'rapport-claireafrique-'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(
            new RapportExport(
                $data['totalVentes'],
                $data['totalProduits'],
                $data['totalLivraison'],
                $data['totalCommandes'],
                $data['topArticles'],
                $data['topClients'],
                $dateDebut,
                $dateFin
            ),
            $nomFichier
        );
    }

    /**
     * Centralise l'extraction et le calcul des métriques du rapport.
     */
    private function getDonneesRapport(?string $dateDebut, ?string $dateFin, ?int $limitTop = null): array
    {
        $totalVentes = Commande::where('statut', '!=', 'annulee')
            ->when($dateDebut, fn ($q) => $q->whereDate('dateCommande', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('dateCommande', '<=', $dateFin))
            ->sum('montantTotal');

        $totalProduits = DB::table('ligne_commandes')
            ->join('commandes', 'ligne_commandes.idCommande', '=', 'commandes.idCommande')
            ->where('commandes.statut', '!=', 'annulee')
            ->when($dateDebut, fn ($q) => $q->whereDate('commandes.dateCommande', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('commandes.dateCommande', '<=', $dateFin))
            ->sum(DB::raw('ligne_commandes.quantite * ligne_commandes.prixUnitaire'));

        $totalLivraison = max(0, $totalVentes - $totalProduits);

        $totalCommandes = Commande::when($dateDebut, fn ($q) => $q->whereDate('dateCommande', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('dateCommande', '<=', $dateFin))
            ->count();

        $commandesParStatut = Commande::selectRaw('statut, COUNT(*) as total')
            ->when($dateDebut, fn ($q) => $q->whereDate('dateCommande', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('dateCommande', '<=', $dateFin))
            ->groupBy('statut')
            ->get();

        $topArticlesQuery = DB::table('ligne_commandes')
            ->join('articles', 'ligne_commandes.idArticle', '=', 'articles.idArticle')
            ->join('commandes', 'ligne_commandes.idCommande', '=', 'commandes.idCommande')
            ->where('commandes.statut', '!=', 'annulee')
            ->when($dateDebut, fn ($q) => $q->whereDate('commandes.dateCommande', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('commandes.dateCommande', '<=', $dateFin))
            ->selectRaw('articles.designation, SUM(ligne_commandes.quantite) as total_vendu,
                         SUM(ligne_commandes.quantite * ligne_commandes.prixUnitaire) as chiffre_affaires')
            ->groupBy('articles.idArticle', 'articles.designation')
            ->orderByDesc('total_vendu');

        if ($limitTop) {
            $topArticlesQuery->limit($limitTop);
        }

        $topClientsQuery = DB::table('utilisateurs')
            ->join('model_has_roles', 'utilisateurs.idUtilisateur', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->join('commandes', 'utilisateurs.idUtilisateur', '=', 'commandes.idUtilisateur')
            ->where('roles.name', 'client')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->where('commandes.statut', '!=', 'annulee')
            ->when($dateDebut, fn ($q) => $q->whereDate('commandes.dateCommande', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('commandes.dateCommande', '<=', $dateFin))
            ->selectRaw('
                utilisateurs.idUtilisateur,
                utilisateurs.nom,
                utilisateurs.prenom,
                COUNT(commandes.idCommande) as commandes_count,
                SUM(commandes.montantTotal) as total_depense
            ')
            ->groupBy('utilisateurs.idUtilisateur', 'utilisateurs.nom', 'utilisateurs.prenom')
            ->orderByDesc('commandes_count');

        if ($limitTop) {
            $topClientsQuery->limit($limitTop);
        }

        return [
            'totalVentes' => $totalVentes,
            'totalProduits' => $totalProduits,
            'totalLivraison' => $totalLivraison,
            'totalCommandes' => $totalCommandes,
            'commandesParStatut' => $commandesParStatut,
            'topArticles' => $topArticlesQuery->get(),
            'topClients' => $topClientsQuery->get(),
        ];
    }
}
