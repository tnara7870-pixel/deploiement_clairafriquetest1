<?php

namespace App\Http\Controllers\Commande;

use App\Exceptions\CommandeAnnulationException;
use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Services\CommandePaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaiementController extends Controller
{
    public function __construct(private CommandePaiementService $commandePaiementService) {}

    public function index()
    {
        $pointVente = Auth::user()->pointVenteAssigne;

        $paiements = Paiement::with(['commande.utilisateur'])
            ->when($pointVente, fn ($q) => $q->whereHas('commande.livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->when(request('statut'), fn ($q) => $q->where('statutPaiement', request('statut'))
            )
            ->when(request('mode'), fn ($q) => $q->where('modePaiement', request('mode'))
            )
            ->when(request('date_debut'), fn ($q) => $q->whereDate('datePaiement', '>=', request('date_debut'))
            )
            ->when(request('date_fin'), fn ($q) => $q->whereDate('datePaiement', '<=', request('date_fin'))
            )
            // Même logique que pour les livraisons : traiter la file de
            // vérification dans l'ordre d'arrivée plutôt que de partir des
            // paiements les plus récents.
            ->oldest('datePaiement')
            ->paginate(5);

        return view('commande.paiements', compact('paiements', 'pointVente'));
    }

    public function update(Request $request, int $id)
    {
        // "rembourse" n'est plus assignable ici : un remboursement ne peut
        // être enregistré que via le formulaire dédié (référence, date,
        // montant), après annulation validée. Voir CommandeAnnulationService.
        $request->validate([
            'statutPaiement' => 'required|in:en_attente,valide,echoue',
        ]);

        try {
            $paiement = Paiement::with('commande.livraison')->findOrFail($id);

            $pointVente = Auth::user()->pointVenteAssigne;
            if ($pointVente && $paiement->commande?->livraison?->pointVenteAttribue !== $pointVente) {
                abort(403, "Ce paiement relève de l'autre point de vente.");
            }

            $this->commandePaiementService->mettreAJourStatut(
                $paiement,
                $request->statutPaiement,
                Auth::user()
            );
        } catch (CommandeAnnulationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Statut du paiement mis à jour.');
    }
}
