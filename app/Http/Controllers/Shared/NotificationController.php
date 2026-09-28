<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Notifications in-app, communes aux quatre espaces (client, admin,
 * stock, commande). Un seul contrôleur : la liste et le comportement
 * sont identiques partout, seul le layout change (déduit du nom de la
 * route courante, comme Shared\CompteController).
 */
class NotificationController extends Controller
{
    /**
     * Page complète listant tout l'historique de notifications de
     * l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $espace = explode('.', $request->route()->getName())[0];

        $notifications = Auth::user()->notifications()->latest()->paginate(20);

        return view($espace.'.notifications', compact('notifications'));
    }

    public function marquerLue(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        $notification?->markAsRead();

        return $this->redirigerVersCommandeOuRetour($request, $notification);
    }

    public function marquerToutesLues(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }

    private function redirigerVersCommandeOuRetour(Request $request, $notification)
    {
        $idCommande = $notification?->data['idCommande'] ?? null;

        if (! $idCommande) {
            return back();
        }

        // La route est recalculée ICI, à partir du rôle ACTUEL de la
        // personne qui clique — au lieu de relire tel quel le
        // 'routeName' figé dans le JSON de la notification au moment de
        // sa création (voir CommandeEvenementNotification::toArray()).
        //
        // Une notification Laravel gèle ses données en JSON à la
        // création et ne les met jamais à jour. Se contenter de relire
        // l'ancien routeName voulait dire qu'une notification créée
        // avant ce correctif — ou avant un changement de rôle de
        // l'utilisateur — continuait à rediriger indéfiniment vers son
        // ancienne destination, même une fois la logique de routage
        // corrigée dans CommandeAnnulationService::notifier(). C'est
        // précisément le bug observé le 06/08/2026 : un clic sur le
        // widget "demande d'annulation" du dashboard (recalculé à
        // l'affichage) menait à /admin/commandes/{id}/annulation, tandis
        // qu'un clic sur LA MÊME notification dans la cloche menait à
        // /commandes-admin/{id} (route figée avant correction).
        //
        // En recalculant systématiquement à partir du rôle courant, ce
        // correctif s'applique du même coup à toutes les notifications
        // déjà en base, sans purge ni migration de données nécessaire.
        $routeName = $this->routeCommandePourRoleActuel();

        if (Route::has($routeName)) {
            return redirect()->route($routeName, $idCommande);
        }

        return back();
    }

    /**
     * Reproduit volontairement la même logique de choix de route que
     * CommandeAnnulationService::notifier() (administrateur → espace
     * admin dédié, res.commande → son propre espace, sinon client). Les
     * deux méthodes doivent rester synchronisées : notifier() choisit la
     * route au moment de l'envoi (utilisée seulement en tout dernier
     * recours si jamais idCommande manque), celle-ci la choisit au
     * moment du clic (la seule qui compte réellement, voir ci-dessus).
     */
    private function routeCommandePourRoleActuel(): string
    {
        $user = Auth::user();

        if ($user->hasRole('administrateur')) {
            return 'admin.commande.annulation';
        }
        if ($user->hasRole('res.commande')) {
            return 'commande.detail';
        }

        return 'client.commande.detail';
    }
}
