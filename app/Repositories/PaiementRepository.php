<?php

namespace App\Repositories;

use App\Models\Paiement;
use App\Models\Panier;

class PaiementRepository
{
    public function findPaymentByToken(string $token): ?Paiement
    {
        return Paiement::where('referenceTransaction', $token)->first();
    }

    public function updatePanierPaydunyaData(Panier $panier, array $data): bool
    {
        return $panier->update($data);
    }
}
