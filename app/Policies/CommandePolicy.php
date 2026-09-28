<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;

/**
 * Autorisation "propriétaire de la ressource" pour les commandes,
 * jusqu'ici faite par un ->where('idUtilisateur', ...) répété
 * manuellement dans chaque méthode de Client\CommandeController.
 * Centraliser ici évite qu'un futur ->where() oublié n'ouvre un IDOR.
 */
class CommandePolicy
{
    public function view(User $user, Commande $commande): bool
    {
        return $user->idUtilisateur === $commande->idUtilisateur;
    }

    public function cancel(User $user, Commande $commande): bool
    {
        return $user->idUtilisateur === $commande->idUtilisateur
            && $commande->estAnnulableParClient();
    }
}
