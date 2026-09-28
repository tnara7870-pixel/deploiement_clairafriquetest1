<?php

namespace App\Http\Controllers\Client;

use App\DTO\CommandeData;
use App\Exceptions\CommandeAnnulationException;
use App\Exceptions\StockInsuffisantException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PreparerCommandeRequest;
use App\Models\Commande;
use App\Models\Panier;
use App\Services\CommandeAnnulationService;
use App\Services\CommandeCreationService;
use App\Services\CommandePreparationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    public function __construct(
        private CommandePreparationService $preparationService,
        private CommandeCreationService $creationService,
        private CommandeAnnulationService $commandeAnnulationService,
    ) {}

    public function index()
    {
        $commandes = Commande::with(['livraison', 'paiement'])
            ->where('idUtilisateur', Auth::user()->idUtilisateur)
            ->when(request('date_debut'), fn ($q) => $q->whereDate('dateCommande', '>=', request('date_debut'))
            )
            ->when(request('date_fin'), fn ($q) => $q->whereDate('dateCommande', '<=', request('date_fin'))
            )
            ->latest('dateCommande')
            ->paginate(10);

        return view('client.commandes', compact('commandes'));
    }

    public function show($id)
    {
        $commande = Commande::with([
            'ligneCommandes.article',
            'livraison',
            'paiement',
            'remboursements',
        ])->findOrFail($id);

        $this->authorize('view', $commande);

        return view('client.commande-detail', compact('commande'));
    }

    public function demanderAnnulation(Request $request, $id)
    {
        $request->validate([
            'motif' => 'required|string|max:500',
        ], [
            'motif.required' => 'Merci de préciser le motif de votre demande d\'annulation.',
        ]);

        $commande = Commande::findOrFail($id);

        $this->authorize('view', $commande);

        try {
            $this->commandeAnnulationService->demanderAnnulation($commande, Auth::user(), $request->motif);
        } catch (CommandeAnnulationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success',
            'Votre demande d\'annulation a bien été enregistrée. '
            .'Elle sera examinée par notre équipe.'
        );
    }

    /**
     * Afficher le récapitulatif avant paiement
     */
    public function recapitulatif()
    {
        $userId = Auth::user()->idUtilisateur;
        $panier = Panier::with('lignePaniers.article')
            ->where('idUtilisateur', $userId)
            ->first();

        if (! $panier || $panier->lignePaniers->isEmpty()) {
            return redirect()->route('client.panier')
                ->with('error', 'Votre panier est vide.');
        }

        $total = $panier->calculerMontant();
        $fraisLivraison = 0;

        return view('client.recapitulatif', compact('panier', 'total', 'fraisLivraison'));
    }

    /**
     * Préparer la commande — stocker en session, rediriger vers paiement ou espèces
     */
    public function preparer(PreparerCommandeRequest $request)
    {
        try {
            $commandeData = $this->preparationService->prepare($request, Auth::user()->idUtilisateur);
        } catch (StockInsuffisantException $e) {
            return redirect()->route('client.panier')->with('error', $e->getMessage());
        }

        if (! $commandeData) {
            return redirect()->route('client.panier')->with('error', 'Votre panier est vide.');
        }

        session(['commande_en_cours' => $commandeData->toArray()]);

        if ($commandeData->modePaiement === 'especes') {
            return redirect()->route('client.commande.especes');
        }

        return redirect()->route('client.paiement.choix');
    }

    public function facture($id)
    {
        $commande = Commande::with([
            'utilisateur',
            'ligneCommandes.article',
            'paiement',
            'livraison',
        ])->findOrFail($id);

        $this->authorize('view', $commande);

        $pdf = Pdf::loadView('pdf.facture', compact('commande'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Facture-'.$commande->numeroCommande.'.pdf');
    }

    /**
     * Afficher la page de confirmation pour paiement en espèces
     */
    public function especes()
    {
        $session = session('commande_en_cours');
        if (! $session) {
            return redirect()->route('client.panier')->with('error', 'Votre session a expiré. Merci de reprendre votre commande depuis le panier.');
        }

        return view('client.especes', compact('session'));
    }

    /**
     * Valider la commande avec paiement en espèces
     */
    public function confirmerEspeces()
    {
        $session = session('commande_en_cours');
        if (! $session) {
            return redirect()->route('client.panier')->with('error', 'Votre session a expiré. Merci de reprendre votre commande depuis le panier.');
        }

        $commandeData = CommandeData::fromArray($session, Auth::user()->idUtilisateur);

        try {
            $commande = $this->creationService->createFromPanier($commandeData);
        } catch (StockInsuffisantException $e) {
            // Le stock a changé entre la préparation de la commande et sa
            // confirmation (concurrence avec un autre client) : on renvoie
            // le client vers son panier avec un message précis plutôt
            // qu'un refus générique. Voir correctif du bug critique 1,
            // audit du 04/08/2026.
            return redirect()->route('client.panier')->with('error', $e->getMessage());
        }

        if (! $commande) {
            // Tentative de récupération d'une commande qui pourrait avoir été
            // créée malgré le retour null (cas observé : la BDD contient la
            // commande mais la méthode a retourné null). Chercher une commande
            // récente correspondant au montant et à l'utilisateur.
            $possible = Commande::where('idUtilisateur', Auth::user()->idUtilisateur)
                ->when($commandeData?->total !== null, fn ($q) => $q->where('montantTotal', $commandeData->total))
                ->where('dateCommande', '>=', now()->subMinutes(5))
                ->latest('dateCommande')
                ->first();

            if ($possible) {
                // Considérer comme succès et informer l'utilisateur.
                session()->forget('commande_en_cours');

                return redirect()->route('client.commande.detail', $possible->idCommande)
                    ->with('success', '✅ Votre commande n° '.$possible->numeroCommande.' a bien été enregistrée.');
            }

            return redirect()->route('client.panier')->with('error', 'Nous n\'avons pas pu enregistrer votre commande. Merci de réessayer ; si le problème persiste, contactez-nous.');
        }

        session()->forget('commande_en_cours');

        return redirect()->route('client.commande.detail', $commande->idCommande)
            ->with('success', '✅ Votre commande n° '.$commande->numeroCommande.' a bien été enregistrée.');
    }
}
