<?php

namespace App\Http\Controllers\Commande;

use App\Exceptions\CommandeAnnulationException;
use App\Http\Controllers\Controller;
use App\Models\Livraison;
use App\Services\LivraisonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivraisonController extends Controller
{
    public function __construct(private LivraisonService $livraisonService) {}

    public function index()
    {
        $pointVente = Auth::user()->pointVenteAssigne;

        $livraisons = Livraison::with(['commande.utilisateur'])
            ->when($pointVente, fn ($q) => $q->where('pointVenteAttribue', $pointVente))
            ->when(request('statut'), fn ($q) => $q->where('statutLivraison', request('statut'))
            )
            ->when(request('mode'), fn ($q) => $q->where('modeLivraison', request('mode'))
            )
            ->when(request('date_debut'), fn ($q) => $q->whereHas('commande', fn ($c) => $c->whereDate('dateCommande', '>=', request('date_debut'))
            )
            )
            ->when(request('date_fin'), fn ($q) => $q->whereHas('commande', fn ($c) => $c->whereDate('dateCommande', '<=', request('date_fin'))
            )
            )
            // Tri du plus ancien au plus récent : une file de suivi se
            // traite dans l'ordre d'arrivée, pas en partant des dernières
            // commandes passées (qui reléguerait les plus anciennes non
            // traitées en fin de pagination, hors de vue).
            ->oldest()
            ->paginate(5);

        return view('commande.livraisons', compact('livraisons', 'pointVente'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'statutLivraison' => 'required|in:preparee,en_livraison,livree',
        ]);

        $pointVente = Auth::user()->pointVenteAssigne;
        if ($pointVente) {
            $livraison = Livraison::findOrFail($id);
            if ($livraison->pointVenteAttribue !== $pointVente) {
                abort(403, "Cette livraison relève de l'autre point de vente.");
            }
        }

        try {
            $this->livraisonService->mettreAJourStatut($id, $request->statutLivraison);
        } catch (CommandeAnnulationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Statut de la livraison mis à jour.');
    }
}
