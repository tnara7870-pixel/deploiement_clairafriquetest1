<?php

namespace App\Repositories;

use App\Models\Commande;
use App\Models\Panier;
use Illuminate\Database\Eloquent\Collection;

class CommandeRepository
{
    public function findById(int $id): ?Commande
    {
        return Commande::with(['ligneCommandes.article', 'livraison', 'paiement', 'remboursements'])
            ->find($id);
    }

    public function findForUser(int $userId): Collection
    {
        return Commande::with(['livraison', 'paiement'])
            ->where('idUtilisateur', $userId)
            ->latest('dateCommande')
            ->get();
    }

    public function createFromData(array $data): Commande
    {
        return Commande::create($data);
    }

    public function createWithNumero(array $attributes): Commande
    {
        return Commande::creerAvecNumero($attributes);
    }

    public function refresh(Commande $commande): Commande
    {
        return $commande->fresh();
    }

    public function findPanierForUser(int $userId): ?Panier
    {
        return Panier::with('lignePaniers.article')
            ->where('idUtilisateur', $userId)
            ->first();
    }
}
