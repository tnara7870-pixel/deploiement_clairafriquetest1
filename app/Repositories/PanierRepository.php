<?php

namespace App\Repositories;

use App\Models\Panier;

class PanierRepository
{
    public function findByUser(int $userId): ?Panier
    {
        return Panier::with('lignePaniers.article.stocksPointVente')
            ->where('idUtilisateur', $userId)
            ->first();
    }

    public function findByUserForUpdate(int $userId): ?Panier
    {
        return Panier::with('lignePaniers.article.stocksPointVente')
            ->where('idUtilisateur', $userId)
            ->lockForUpdate()
            ->first();
    }

    public function clearPaydunyaData(Panier $panier): bool
    {
        return $panier->update([
            'paydunyaTokenEnAttente' => null,
            'paydunyaInvoiceUrlEnAttente' => null,
            'paydunyaTokenExpireA' => null,
            'paydunyaModePaiementEnAttente' => null,
        ]);
    }

    public function clearContents(Panier $panier): void
    {
        $panier->lignePaniers()->delete();
    }
}
