<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\PaiementDomainService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    public function __construct(private PaiementDomainService $paiementService) {}

    public function choix(): mixed
    {
        $session = session('commande_en_cours');

        if (! $session) {
            return redirect()->route('client.panier')
                ->with('error', 'Votre session a expiré. Merci de reprendre votre commande depuis le panier.');
        }

        if (($session['modePaiement'] ?? null) === 'especes') {
            return redirect()->route('client.commande.especes');
        }

        $userId = Auth::user()->idUtilisateur;

        try {
            $invoiceUrl = DB::transaction(function () use ($userId, $session) {
                return $this->paiementService->getInvoiceUrlForUser($userId, $session);
            });
        } catch (\Throwable $e) {
            Log::error('Échec inattendu lors de la création de la facture PayDunya', [
                'idUtilisateur' => $userId,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('client.recapitulatif')
                ->with('error', 'Le service de paiement est momentanément indisponible. Veuillez réessayer dans quelques instants.');
        }

        if ($invoiceUrl === null) {
            return redirect()->route('client.panier')
                ->with('error', 'Votre panier est vide.');
        }

        if ($invoiceUrl === '') {
            return redirect()->route('client.recapitulatif')
                ->with('error', 'Impossible de générer votre facture de paiement pour le moment. Merci de réessayer, ou de choisir un autre mode de paiement.');
        }

        return redirect($invoiceUrl);
    }

    public function retour(Request $request): mixed
    {
        $token = $request->get('token') ?? session('paydunya_token');

        if (! $token) {
            return redirect()->route('client.panier')
                ->with('error', 'Votre lien de paiement n\'est plus valide ou a expiré. Merci de reprendre votre commande depuis le panier.');
        }

        $invoice = $this->paiementService->confirmerInvoice($token);

        if (! $invoice) {
            return redirect()->route('client.recapitulatif')
                ->with('error', 'Nous n\'avons pas pu vérifier votre paiement auprès du prestataire. Merci de réessayer dans quelques instants ; si le montant a été débité malgré tout, contactez-nous.');
        }

        $idUtilisateurInvoice = $invoice->getCustomData('idUtilisateur');

        if (! Auth::check() || (int) $idUtilisateurInvoice !== (int) Auth::user()->idUtilisateur) {
            if (! Auth::check()) {
                return redirect()->route('login')
                    ->with('error', 'Veuillez vous reconnecter pour consulter votre commande.');
            }

            abort(403, 'Accès non autorisé.');
        }

        if ($invoice->getStatus() !== 'completed') {
            return redirect()->route('client.recapitulatif')
                ->with('error', 'Votre paiement n\'a pas été confirmé (en attente ou refusé par votre opérateur). Vous pouvez réessayer, ou choisir le paiement à la livraison.');
        }

        $commande = $this->paiementService->creerCommandeDepuisInvoice($invoice, $token);

        if (! $commande) {
            return redirect()->route('client.commandes')
                ->with('error', 'Votre paiement a bien été reçu, mais nous rencontrons un problème pour finaliser votre commande. Notre équipe va la traiter manuellement et vous contactera rapidement — conservez la référence de votre paiement.');
        }

        session()->forget(['commande_en_cours', 'paydunya_token']);

        return redirect()->route('client.commande.detail', $commande->idCommande)
            ->with('success',
                '✅ Paiement confirmé ! Commande '.
                $commande->numeroCommande.' validée.'
            );
    }

    public function ipn(Request $request): mixed
    {
        $token = $request->get('token');

        if (! $token) {
            return response()->json(['status' => 'error', 'message' => 'Token manquant'], 400);
        }

        $commande = $this->paiementService->confirmerToken($token);

        if (! $commande) {
            return response()->json(['status' => 'pending'], 200);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function annulation(): mixed
    {
        session()->forget(['commande_en_cours', 'paydunya_token']);

        $this->paiementService->annulerPaiementEnCours(Auth::user()->idUtilisateur);

        return redirect()->route('client.panier')
            ->with('error', '❌ Paiement annulé. Votre panier a été conservé.');
    }
}
