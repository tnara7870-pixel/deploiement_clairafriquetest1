<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    public function view(User $user, Paiement $paiement): bool
    {
        return $user->hasRole('administrateur');
    }

    public function update(User $user, Paiement $paiement): bool
    {
        return $user->hasRole('administrateur');
    }
}
