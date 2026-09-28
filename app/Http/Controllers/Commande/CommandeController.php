<?php

namespace App\Http\Controllers\Commande;

use App\Exceptions\CommandeAnnulationException;
use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\HistoriqueCommande;
use App\Services\CommandeAnnulationService;
use App\Services\CommandeStatutService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    public function __construct(
        private CommandeStatutService $commandeStatutService,
        private CommandeAnnulationService $commandeAnnulationService,
    ) {}

    /**
     * Un compte res.commande scopé à un point de vente ne doit ni voir ni
     * agir sur une commande qui relève de l'autre point — que ce soit
     * dans la liste (filtrée ici) ou en accédant directement à son URL
     * (bloqué par verifierAccesPointVente ci-dessous, appelée dans
     * chaque action). Un compte non scopé garde l'accès global actuel.
     */
    public function index()
    {
        $pointVente = Auth::user()->pointVenteAssigne;

        $commandes = Commande::with(['utilisateur', 'paiement', 'livraison'])
            ->when($pointVente, fn ($q) => $q->whereHas('livraison', fn ($l) => $l->where('pointVenteAttribue', $pointVente))
            )
            ->when(request('statut'), fn ($q) => $q->where('statut', request('statut'))
            )
            ->when(request('search'), function ($q) {
                $terme = request('search');
                $q->where(function ($sub) use ($terme) {
                    $sub->where('numeroCommande', 'like', "%{$terme}%")
                        ->orWhereHas('utilisateur', function ($u) use ($terme) {
                            $u->where('nom', 'like', "%{$terme}%")
                                ->orWhere('prenom', 'like', "%{$terme}%");
                        });
                });
            })
            ->when(request('date_debut'), fn ($q) => $q->whereDate('dateCommande', '>=', request('date_debut'))
            )
            ->when(request('date_fin'), fn ($q) => $q->whereDate('dateCommande', '<=', request('date_fin'))
            )
            ->latest('dateCommande')
            ->paginate(5)
            ->withQueryString();

        return view('commande.liste', compact('commandes', 'pointVente'));
    }

    /**
     * Bloque l'accès direct par URL à une commande d'un autre point de
     * vente pour un compte scopé — sans ça, le filtrage de index() ne
     * serait qu'un confort d'affichage, pas une vraie séparation.
     */
    private function verifierAccesPointVente(Commande $commande): void
    {
        $pointVente = Auth::user()->pointVenteAssigne;

        if ($pointVente && $commande->livraison?->pointVenteAttribue !== $pointVente) {
            abort(403, "Cette commande relève de l'autre point de vente.");
        }
    }

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

        $this->verifierAccesPointVente($commande);

        return view('commande.detail', compact('commande'));
    }

    public function updateStatut(Request $request, int $id)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,validee,en_livraison,livree',
        ]);

        $commande = Commande::with('livraison')->findOrFail($id);
        $this->verifierAccesPointVente($commande);

        try {
            $this->commandeStatutService->changerStatut($commande, $request->statut, Auth::user());
        } catch (CommandeAnnulationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success',
            'Statut de la commande mis à jour : '.Commande::libelleStatut($request->statut).'.'
        );
    }

    public function validerAnnulation(int $id)
    {
        $commande = Commande::with(['ligneCommandes.article', 'paiement', 'livraison', 'utilisateur'])
            ->findOrFail($id);

        $this->verifierAccesPointVente($commande);

        try {
            $this->commandeAnnulationService->validerAnnulation($commande, Auth::user());
        } catch (CommandeAnnulationException $e) {
            return back()->with('error', $e->getMessage());
        }

        $commande->refresh();
        $message = 'Annulation validée : le stock a été restitué.';
        if ($commande->necessiteRemboursement()) {
            $message .= ' Un remboursement reste à enregistrer.';
        }

        return back()->with('success', $message);
    }

    public function refuserAnnulation(Request $request, int $id)
    {
        $request->validate([
            'motifRefus' => 'required|string|max:500',
        ], [
            'motifRefus.required' => 'Merci d\'indiquer le motif du refus.',
        ]);

        $commande = Commande::with(['utilisateur', 'livraison'])->findOrFail($id);
        $this->verifierAccesPointVente($commande);

        try {
            $this->commandeAnnulationService->refuserAnnulation($commande, Auth::user(), $request->motifRefus);
        } catch (CommandeAnnulationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Demande d\'annulation refusée. La commande reprend son cours normal.');
    }

    public function enregistrerRemboursement(Request $request, int $id)
    {
        $request->validate([
            'referenceTransaction' => 'required|string|max:100',
            'dateRemboursement' => 'required|date',
            'montant' => 'required|numeric|min:1',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $commande = Commande::with(['paiement', 'livraison'])->findOrFail($id);
        $this->verifierAccesPointVente($commande);

        $this->authorize('update', $commande->paiement);

        try {
            $this->commandeAnnulationService->enregistrerRemboursement(
                $commande,
                Auth::user(),
                $request->referenceTransaction,
                $request->dateRemboursement,
                (float) $request->montant,
                $request->commentaire,
            );
        } catch (CommandeAnnulationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Remboursement enregistré avec succès.');
    }

    public function resoudreProblemeStock(Request $request, int $id)
    {
        $commande = Commande::with('livraison')->findOrFail($id);
        $this->verifierAccesPointVente($commande);

        HistoriqueCommande::create([
            'idCommande' => $commande->idCommande,
            'statutPrecedent' => $commande->statut,
            'statutNouveau' => $commande->statut,
            'action' => 'probleme_stock_resolu',
            'idUtilisateur' => Auth::user()->idUtilisateur,
            'commentaire' => $commande->problemeStockDetails,
            'dateAction' => now(),
        ]);

        $commande->update([
            'problemeStock' => false,
            'problemeStockDetails' => null,
        ]);

        return back()->with('success', 'Signalement de rupture de stock marqué comme traité.');
    }

    public function facture(int $id)
    {
        $commande = Commande::with([
            'utilisateur',
            'ligneCommandes.article',
            'paiement',
            'livraison',
        ])->findOrFail($id);

        $this->verifierAccesPointVente($commande);

        $pdf = Pdf::loadView('pdf.facture', compact('commande'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Facture-'.$commande->numeroCommande.'.pdf');
    }
}
