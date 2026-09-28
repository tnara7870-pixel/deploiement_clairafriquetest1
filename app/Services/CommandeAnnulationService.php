<?php

namespace App\Services;

use App\Exceptions\CommandeAnnulationException;
use App\Models\Commande;
use App\Models\HistoriqueCommande;
use App\Models\MouvementStock;
use App\Models\Remboursement;
use App\Models\StockPointVente;
use App\Models\User;
use App\Notifications\CommandeEvenementNotification;
use Illuminate\Support\Facades\DB;

/**
 * Service unique pour tout le workflow d'annulation d'une commande :
 * demande côté client, décision (validation/refus) côté administrateur,
 * et enregistrement du remboursement manuel. Centralise les règles
 * métier d'éligibilité, la restitution de stock, la traçabilité
 * (historique_commandes) et les notifications in-app, pour éviter que
 * cette logique ne se disperse — et diverge — entre plusieurs
 * contrôleurs.
 *
 * Les changements de statut de progression logistique "normale"
 * (en_attente → validee → en_livraison → livree) restent gérés par
 * CommandeStatutService ; ce service-ci ne s'occupe que de la branche
 * annulation/remboursement, qui a des règles et des acteurs différents
 * (seul l'administrateur décide — voir demanderAnnulation/valider/
 * refuser ci-dessous).
 */
class CommandeAnnulationService
{
    /**
     * Le client demande l'annulation de sa commande. Ne touche ni au
     * stock ni au paiement : seule la validation par l'administrateur
     * déclenche ces effets.
     */
    public function demanderAnnulation(Commande $commande, User $client, string $motif): void
    {
        // Vérification rapide hors transaction (évite de verrouiller la ligne
        // pour rien dans le cas normal où la commande n'est déjà pas éligible).
        if (! $commande->estAnnulableParClient()) {
            throw new CommandeAnnulationException(
                'Cette commande ne peut plus être annulée directement (elle est déjà en cours de livraison, livrée, annulée, ou une demande est déjà en cours). Contactez-nous si besoin.'
            );
        }

        DB::transaction(function () use ($commande, $client, $motif) {
            // Revérifie l'état sous verrou : sans ce lockForUpdate, un
            // double-clic (deux requêtes quasi simultanées) passait toutes
            // les deux la vérification ci-dessus et créait chacune une
            // entrée d'historique + une notification au personnel pour la
            // même demande. L'état final restait cohérent (statut
            // 'demande_annulation' une seule fois en base), mais générait du
            // bruit dupliqué dans l'historique et les notifications.
            $commandeVerrouillee = Commande::lockForUpdate()->findOrFail($commande->idCommande);

            if (! $commandeVerrouillee->estAnnulableParClient()) {
                throw new CommandeAnnulationException(
                    'Cette demande a déjà été prise en compte (double clic ?). Rechargez la page.'
                );
            }

            $statutPrecedent = $commandeVerrouillee->statut;

            $commandeVerrouillee->update([
                'statutAvantAnnulation' => $statutPrecedent,
                'statut' => 'demande_annulation',
                'motifAnnulation' => $motif,
            ]);

            $this->historiser($commandeVerrouillee, $statutPrecedent, 'demande_annulation', 'demande_annulation', $client, $motif);
        });

        // Le client attend une confirmation ; le personnel (admin, seul
        // décisionnaire, + res.commande pour information logistique)
        // doit savoir qu'une action est attendue.
        $this->notifier($client, $commande,
            'Demande d\'annulation enregistrée',
            'Votre demande d\'annulation pour la commande '.$commande->numeroCommande.' a bien été enregistrée. Elle sera examinée par notre équipe.',
            'client.commande.detail'
        );

        foreach ($this->personnelAInformer() as $membre) {
            $this->notifier($membre, $commande,
                'Nouvelle demande d\'annulation',
                'Le client '.$client->prenom.' '.$client->nom.' demande l\'annulation de la commande '.$commande->numeroCommande.'.',
                // Ce paramètre est en réalité ignoré par notifier() tant que
                // $membre n'est pas null (elle recalcule la route depuis le
                // rôle du destinataire) — mais laisser ici une valeur qui ne
                // correspond à la destination réelle QUE pour res.commande,
                // jamais pour l'administrateur, est exactement ce qui a
                // rendu ce flux illisible et a fini par causer une
                // régression (bug du 06/08/2026, voir NotificationController
                // ::redirigerVersCommandeOuRetour()). 'admin.commande.annulation'
                // documente ici la destination réelle pour un administrateur ;
                // notifier() la remplace par 'commande.detail' pour un
                // destinataire res.commande.
                'admin.commande.annulation'
            );
        }

        // Pas de notification "remboursement à effectuer" ici : à ce stade,
        // la commande vient tout juste de passer à 'demande_annulation', son
        // statut ne peut pas encore être 'annulee', donc
        // necessiteRemboursement() est structurellement toujours faux ici.
        // Cette notification est envoyée au bon moment dans
        // validerAnnulation() ci-dessous, une fois l'annulation
        // effectivement actée par l'administrateur.
    }

    /**
     * L'administrateur valide la demande : restitution du stock,
     * synchronisation de la livraison, traçabilité. Le remboursement
     * (si nécessaire) est une étape séparée et volontaire — voir
     * enregistrerRemboursement() — jamais automatique.
     */
    public function validerAnnulation(Commande $commande, User $admin): void
    {
        if (! $commande->aUneDemandeAnnulationEnCours()) {
            throw new CommandeAnnulationException(
                "Cette commande n'a pas de demande d'annulation en attente."
            );
        }

        DB::transaction(function () use ($commande, $admin) {
            // Verrouille et revérifie l'état à l'intérieur de la transaction :
            // évite que deux administrateurs validant/refusant la même
            // demande en même temps ne restituent le stock deux fois.
            $commandeVerrouillee = Commande::lockForUpdate()->findOrFail($commande->idCommande);

            if (! $commandeVerrouillee->aUneDemandeAnnulationEnCours()) {
                throw new CommandeAnnulationException(
                    "Cette demande d'annulation vient d'être traitée par quelqu'un d'autre."
                );
            }

            $statutPrecedent = $commandeVerrouillee->statut;
            $this->annulerEtRestituerStock($commandeVerrouillee, $admin);
            $this->historiser($commandeVerrouillee, $statutPrecedent, 'annulee', 'annulation_validee', $admin, null);
        });

        $commande->refresh()->load('paiement');

        $message = 'Votre commande '.$commande->numeroCommande.' a été annulée. Le stock a été restitué.';
        if ($commande->necessiteRemboursement()) {
            $message .= ' Un remboursement sera effectué prochainement.';
        }

        $this->notifier($commande->utilisateur, $commande, 'Annulation acceptée', $message, 'client.commande.detail');

        // Le personnel doit être informé quand cette annulation fait naître
        // un remboursement à traiter (paiement déjà encaissé) : sans cela,
        // seul le client était notifié et le widget "commandesARembourser"
        // du dashboard admin restait la seule source de vérité, découverte
        // uniquement en s'y rendant manuellement.
        if ($commande->necessiteRemboursement()) {
            foreach ($this->personnelAInformer() as $membre) {
                $this->notifier($membre, $commande,
                    'Remboursement à effectuer',
                    'La commande '.$commande->numeroCommande.' est annulée et nécessite un remboursement de '
                        .number_format($commande->paiement->montant, 0, ',', ' ').' F CFA.',
                    'admin.commande.annulation'
                );
            }
        }
    }

    /**
     * Annulation déclenchée automatiquement par le système (et non par
     * une demande client suivie d'une décision admin) quand un paiement
     * en attente est marqué comme échoué : la commande n'a jamais été
     * confirmée, il n'y a donc rien à arbitrer, seulement le stock à
     * restituer. Utilisée par Commande\PaiementController (acteur =
     * l'administrateur qui constate l'échec) et par la commande
     * planifiée ExpirerCommandesEspecesNonHonorees (acteur = null,
     * déclenchement système sans utilisateur humain identifiable).
     */
    public function annulerPourEchecPaiement(Commande $commande, ?User $acteur): void
    {
        if (! in_array($commande->statut, ['en_attente'], true)) {
            throw new CommandeAnnulationException(
                'Cette commande ne peut pas être annulée automatiquement pour échec de paiement (statut actuel incompatible).'
            );
        }

        DB::transaction(function () use ($commande, $acteur) {
            // Reverrouille et revérifie l'état À L'INTÉRIEUR de la
            // transaction (même pattern que refuserAnnulation() juste en
            // dessous) : la vérification faite plus haut, avant la
            // transaction, peut être basée sur un état déjà périmé si un
            // autre déclenchement concurrent (ex. la commande planifiée
            // ExpirerCommandesEspecesNonHonorees tournant en même temps
            // qu'une action admin manuelle) a déjà traité cette commande
            // entre-temps. Sans ce verrou, le stock pourrait être restitué
            // deux fois pour la même commande.
            $commandeVerrouillee = Commande::lockForUpdate()->findOrFail($commande->idCommande);

            if (! in_array($commandeVerrouillee->statut, ['en_attente'], true)) {
                throw new CommandeAnnulationException(
                    'Cette commande a déjà été traitée entre-temps.'
                );
            }

            $statutPrecedent = $commandeVerrouillee->statut;
            $this->annulerEtRestituerStock($commandeVerrouillee, $acteur);
            $this->historiser($commandeVerrouillee, $statutPrecedent, 'annulee', 'annulation_validee', $acteur, 'Échec du paiement');
        });

        $this->notifier($commande->utilisateur, $commande,
            'Commande annulée',
            'Votre commande '.$commande->numeroCommande.' a été annulée suite à un échec de paiement.',
            'client.commande.detail'
        );
    }

    /**
     * Bascule la commande en "annulee", restitue le stock de chaque
     * ligne et synchronise la livraison. Factorisé entre
     * validerAnnulation() et annulerPourEchecPaiement() pour ne pas
     * dupliquer cette mécanique. $acteur est nullable pour les
     * déclenchements automatiques par le système.
     */
    private function annulerEtRestituerStock(Commande $commande, ?User $acteur): void
    {
        $commande->update(['statut' => 'annulee']);

        // Restitue chaque ligne au point de vente qui l'avait fournie
        // (pointVenteAttribue, figé à la création de la commande) — pas à
        // l'entrepôt principal ACTUEL si la config a changé depuis, et pas
        // au total global directement.
        $pointVenteAttribue = $commande->livraison?->pointVenteAttribue
            ?? config('pointvente.entrepot_principal');

        foreach ($commande->ligneCommandes as $ligne) {
            $stockPoint = StockPointVente::where('idArticle', $ligne->idArticle)
                ->where('pointVente', $pointVenteAttribue)
                ->lockForUpdate()
                ->first();

            if ($stockPoint) {
                $stockPoint->increment('quantiteStock', $ligne->quantite);
            } else {
                $stockPoint = StockPointVente::create([
                    'idArticle' => $ligne->idArticle,
                    'pointVente' => $pointVenteAttribue,
                    'quantiteStock' => $ligne->quantite,
                ]);
            }

            $ligne->article->resynchroniserQuantiteTotale();

            MouvementStock::create([
                'typeMouvement' => 'retour',
                'idArticle' => $ligne->idArticle,
                'pointVente' => $pointVenteAttribue,
                'quantite' => $ligne->quantite,
                'motif' => 'Annulation commande '.$commande->numeroCommande,
                'idUtilisateur' => $acteur?->idUtilisateur,
                'idCommande' => $commande->idCommande,
            ]);
        }

        $commande->livraison?->update(['statutLivraison' => 'annulee']);
    }

    /**
     * L'administrateur refuse la demande : la commande retrouve son
     * statut d'avant la demande. Le motif du refus est obligatoire et
     * conservé dans l'historique.
     */
    public function refuserAnnulation(Commande $commande, User $admin, string $motifRefus): void
    {
        if (! $commande->aUneDemandeAnnulationEnCours()) {
            throw new CommandeAnnulationException(
                "Cette commande n'a pas de demande d'annulation en attente."
            );
        }

        DB::transaction(function () use ($commande, $admin, $motifRefus) {
            $commandeVerrouillee = Commande::lockForUpdate()->findOrFail($commande->idCommande);

            if (! $commandeVerrouillee->aUneDemandeAnnulationEnCours()) {
                throw new CommandeAnnulationException(
                    "Cette demande d'annulation vient d'être traitée par quelqu'un d'autre."
                );
            }

            $statutRetabli = $commandeVerrouillee->statutAvantAnnulation ?? 'validee';

            $commandeVerrouillee->update([
                'statut' => $statutRetabli,
                'motifAnnulation' => null,
                'statutAvantAnnulation' => null,
            ]);

            $this->historiser($commandeVerrouillee, 'demande_annulation', $statutRetabli, 'annulation_refusee', $admin, $motifRefus);
        });

        $this->notifier($commande->utilisateur, $commande,
            'Demande d\'annulation refusée',
            'Votre demande d\'annulation pour la commande '.$commande->numeroCommande.' a été refusée. Motif : '.$motifRefus,
            'client.commande.detail'
        );
    }

    /**
     * Enregistre un remboursement effectué manuellement par
     * l'administrateur depuis le tableau de bord PayDunya (l'API du
     * prestataire ne le fait pas automatiquement). Formulaire complet
     * plutôt qu'une case à cocher, pour garder une preuve exploitable.
     */
    public function enregistrerRemboursement(
        Commande $commande,
        User $admin,
        string $referenceTransaction,
        string $dateRemboursement,
        float $montant,
        ?string $commentaire = null,
    ): Remboursement {
        if (! $commande->necessiteRemboursement()) {
            throw new CommandeAnnulationException(
                "Cette commande n'est pas éligible à un remboursement (elle n'est pas annulée, le paiement n'a pas été encaissé, ou un remboursement a déjà été enregistré)."
            );
        }

        $remboursement = DB::transaction(function () use (
            $commande, $admin, $referenceTransaction, $dateRemboursement, $montant, $commentaire
        ) {
            $remboursement = Remboursement::create([
                'idCommande' => $commande->idCommande,
                'idPaiement' => $commande->paiement?->idPaiement,
                'referenceTransaction' => $referenceTransaction,
                'dateRemboursement' => $dateRemboursement,
                'montant' => $montant,
                'commentaire' => $commentaire,
                'idUtilisateur' => $admin->idUtilisateur,
            ]);

            $commande->paiement?->update(['statutPaiement' => 'rembourse']);

            $this->historiser(
                $commande, $commande->statut, $commande->statut, 'remboursement_enregistre', $admin,
                'Réf. '.$referenceTransaction.' — '.number_format($montant, 0, ',', ' ').' F CFA'.
                    ($commentaire ? ' — '.$commentaire : '')
            );

            return $remboursement;
        });

        $this->notifier($commande->utilisateur, $commande,
            'Remboursement effectué',
            'Le remboursement de votre commande '.$commande->numeroCommande.' ('.number_format($montant, 0, ',', ' ').' F CFA) a été enregistré.',
            'client.commande.detail'
        );

        return $remboursement;
    }

    private function historiser(
        Commande $commande,
        ?string $statutPrecedent,
        string $statutNouveau,
        string $action,
        ?User $acteur,
        ?string $commentaire,
    ): void {
        HistoriqueCommande::create([
            'idCommande' => $commande->idCommande,
            'statutPrecedent' => $statutPrecedent,
            'statutNouveau' => $statutNouveau,
            'action' => $action,
            'idUtilisateur' => $acteur?->idUtilisateur,
            'commentaire' => $commentaire,
            'dateAction' => now(),
        ]);
    }

    private function notifier(?User $destinataire, Commande $commande, string $titre, string $message, string $routeName): void
    {
        // Choisir dynamiquement la route de destination selon le rôle du
        // destinataire : les administrateurs doivent rester dans leur
        // espace /admin, les responsables commande dans /commandes-admin,
        // et le client dans son espace client. Cela évite qu'un
        // administrateur soit redirigé vers l'interface "responsable" et
        // s'y retrouve piégé (K3).
        if ($destinataire) {
            if ($destinataire->hasRole('administrateur')) {
                $route = 'admin.commande.annulation';
            } elseif ($destinataire->hasRole('res.commande')) {
                $route = 'commande.detail';
            } else {
                $route = 'client.commande.detail';
            }
        } else {
            $route = $routeName;
        }

        $destinataire?->notify(new CommandeEvenementNotification($commande, $titre, $message, $route));
    }

    /**
     * Personnel à informer d'une nouvelle demande d'annulation :
     * administrateurs (seuls décisionnaires) et responsables commande
     * (pour suspendre la logistique si besoin — le blocage effectif est
     * de toute façon garanti par CommandeStatutService).
     */
    private function personnelAInformer()
    {
        return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['administrateur', 'res.commande']))->get();
    }
}
