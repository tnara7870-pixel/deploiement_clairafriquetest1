<?php

namespace App\Services;

use App\Models\Article;
use App\Models\MouvementStock;
use App\Models\StockPointVente;
use App\Repositories\MouvementStockRepository;
use Illuminate\Support\Facades\DB;

class MouvementStockService
{
    public function __construct(private MouvementStockRepository $repository) {}

    /**
     * $data doit contenir 'pointVente' (ucad|centre_ville) en plus des
     * champs habituels : un mouvement agit toujours sur le stock d'UN
     * point de vente précis, jamais sur le total global directement.
     * articles.quantiteStock est recalculé en fin d'opération pour
     * rester la somme exacte des deux points.
     */
    public function createMovement(int $userId, array $data): Article
    {
        $article = DB::transaction(function () use ($userId, $data) {
            $article = Article::lockForUpdate()->findOrFail($data['idArticle']);

            $stockPoint = StockPointVente::where('idArticle', $data['idArticle'])
                ->where('pointVente', $data['pointVente'])
                ->lockForUpdate()
                ->first();

            if (! $stockPoint) {
                $stockPoint = StockPointVente::create([
                    'idArticle' => $data['idArticle'],
                    'pointVente' => $data['pointVente'],
                    'quantiteStock' => 0,
                ]);
            }

            if ($data['typeMouvement'] === 'sortie' && $stockPoint->quantiteStock < $data['quantite']) {
                throw new \RuntimeException(
                    'Stock insuffisant sur ce point de vente. Disponible : '.$stockPoint->quantiteStock
                );
            }

            $deltaEnregistre = match ($data['typeMouvement']) {
                'entree', 'retour' => $data['quantite'],
                'sortie' => -$data['quantite'],
                'ajustement' => $data['quantite'] - $stockPoint->quantiteStock,
                default => throw new \RuntimeException('Type de mouvement invalide.'),
            };

            if ($data['typeMouvement'] === 'ajustement' && $deltaEnregistre === 0) {
                return $article;
            }

            MouvementStock::create([
                'typeMouvement' => $data['typeMouvement'],
                'idArticle' => $data['idArticle'],
                'pointVente' => $data['pointVente'],
                'quantite' => $deltaEnregistre,
                'motif' => $data['motif'] ?? null,
                'idUtilisateur' => $userId,
            ]);

            match ($data['typeMouvement']) {
                'entree', 'retour' => $stockPoint->increment('quantiteStock', $data['quantite']),
                'sortie' => $stockPoint->decrement('quantiteStock', $data['quantite']),
                'ajustement' => $stockPoint->update(['quantiteStock' => $data['quantite']]),
            };

            $article->resynchroniserQuantiteTotale();

            return $article;
        });

        return $article;
    }
}
