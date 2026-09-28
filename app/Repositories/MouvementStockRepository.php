<?php

namespace App\Repositories;

use App\Models\Article;
use App\Models\MouvementStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MouvementStockRepository
{
    public function paginate(array $filters, int $perPage = 5): LengthAwarePaginator
    {
        return MouvementStock::with(['article', 'utilisateur', 'commande'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('typeMouvement', $type))
            ->when($filters['pointVente'] ?? null, fn ($q, $pv) => $q->where('pointVente', $pv))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('article', fn ($a) => $a->where('designation', 'like', '%'.$search.'%')
            )
            )
            ->when($filters['date_debut'] ?? null, fn ($q, $date) => $q->whereDate('dateMouvement', '>=', $date)
            )
            ->when($filters['date_fin'] ?? null, fn ($q, $date) => $q->whereDate('dateMouvement', '<=', $date)
            )
            ->latest('dateMouvement')
            ->paginate($perPage);
    }

    public function getActiveArticles()
    {
        return Article::with('stocksPointVente')
            ->where('statut', true)
            ->orderBy('designation')
            ->get();
    }
}
