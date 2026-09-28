<?php

namespace App\Repositories;

use App\Models\Livraison;

class LivraisonRepository
{
    public function findByIdForUpdate(int $id): ?Livraison
    {
        return Livraison::with(['commande.ligneCommandes.article', 'commande.paiement'])
            ->lockForUpdate()
            ->find($id);
    }
}
