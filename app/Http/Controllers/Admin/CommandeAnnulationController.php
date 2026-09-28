<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Services\CommandeAnnulationService;

class CommandeAnnulationController extends Controller
{
    public function __construct(private CommandeAnnulationService $commandeAnnulationService) {}

    public function show(int $id)
    {
        $commande = Commande::with([
            'utilisateur',
            'ligneCommandes.article',
            'paiement',
            'livraison',
            'remboursements',
            'historique.utilisateur',
        ])->findOrFail($id);

        return view('admin.commande-annulation', compact('commande'));
    }
}
