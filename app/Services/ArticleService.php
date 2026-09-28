<?php

namespace App\Services;

use App\Models\Article;
use App\Models\MouvementStock;
use App\Models\SeuilAlerte;
use App\Models\StockPointVente;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    public function __construct(private ArticleRepository $articleRepository) {}

    /**
     * @param  array  $data  Colonnes de la table articles (hors quantiteStock,
     *                       jamais écrite directement — voir plus bas).
     * @param  int  $quantiteInitiale  Stock de départ, tracé comme un
     *                                 MouvementStock d'entrée (traçabilité obligatoire,
     *                                 voir audit du 02/08/2026, bug majeur 4).
     * @param  int|null  $seuilMinimal  Seuil d'alerte optionnel à créer.
     */
    /**
     * @param  string|null  $pointVenteInitial  Point de vente qui reçoit le
     *                                          stock de départ. Par défaut : le point assigné au créateur s'il
     *                                          est scopé, sinon l'entrepôt principal configuré. Le catalogue
     *                                          reste unique — c'est uniquement la répartition du stock initial
     *                                          qui varie.
     */
    public function createArticle(
        array $data,
        int $quantiteInitiale = 0,
        ?int $seuilMinimal = null,
        ?string $pointVenteInitial = null,
    ): Article {
        return DB::transaction(function () use ($data, $quantiteInitiale, $seuilMinimal, $pointVenteInitial) {
            $article = $this->articleRepository->create([...$data, 'quantiteStock' => 0]);

            $pointVenteInitial ??= Auth::user()->pointVenteAssigne
                ?? config('pointvente.entrepot_principal');

            // Les deux lignes de stock par point sont créées automatiquement
            // par Article::booted() (hook 'created') dès l'insertion
            // ci-dessus, quel que soit le code appelant — plus besoin de le
            // refaire ici. On ne s'occupe plus que de la répartition du
            // stock de départ.
            if ($quantiteInitiale > 0) {
                MouvementStock::create([
                    'typeMouvement' => 'entree',
                    'idArticle' => $article->idArticle,
                    'pointVente' => $pointVenteInitial,
                    'quantite' => $quantiteInitiale,
                    'motif' => 'Stock initial à la création de l\'article',
                    'idUtilisateur' => Auth::user()->idUtilisateur,
                ]);

                StockPointVente::where('idArticle', $article->idArticle)
                    ->where('pointVente', $pointVenteInitial)
                    ->increment('quantiteStock', $quantiteInitiale);

                $article->resynchroniserQuantiteTotale();
            }

            if (! empty($seuilMinimal)) {
                SeuilAlerte::create([
                    'idArticle' => $article->idArticle,
                    'quantiteMinimale' => $seuilMinimal,
                    'estActif' => true,
                ]);
            }

            return $article;
        });
    }

    public function updateArticle(Article $article, array $data): bool
    {
        return $this->articleRepository->update($article, $data);
    }
}
