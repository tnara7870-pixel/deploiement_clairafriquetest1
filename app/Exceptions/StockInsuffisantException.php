<?php

namespace App\Exceptions;

use App\Models\Article;
use RuntimeException;

/**
 * Levée quand le stock réel, revérifié sous verrou (Article::lockForUpdate())
 * au moment de la création effective de la commande, s'avère insuffisant —
 * y compris si une vérification préalable, non verrouillée, l'avait jugé
 * suffisant quelques instants plus tôt (fenêtre de concurrence entre deux
 * clients qui commandent le même article au même moment).
 *
 * Contrairement au chemin PayDunya (où l'argent a déjà été encaissé et où
 * la commande doit donc être créée quand même, signalée via
 * Commande::problemeStock pour traitement manuel — voir
 * PaiementDomainService::creerCommandeDepuisInvoice()), le paiement en
 * espèces n'a, à ce stade, encore rien encaissé : il est à la fois possible
 * et préférable d'annuler proprement toute la transaction et de renvoyer le
 * client vers son panier plutôt que de créer une commande vouée à un
 * problème de stock. Le message est déjà formulé pour être affiché tel
 * quel à l'utilisateur. Voir audit du 04/08/2026, correctif du bug
 * critique 1.
 */
class StockInsuffisantException extends RuntimeException
{
    /**
     * Construit un message précis plutôt qu'un "stock épuisé" générique :
     * si l'article est en réalité disponible à l'AUTRE point en quantité
     * suffisante, on le dit explicitement — un client qui se voit répondre
     * "épuisé" alors que l'article est disponible juste à l'autre point de
     * vente ne peut rien en faire d'utile. Utilisée aussi bien par le
     * chemin espèces (CommandeCreationService) que par le chemin paiement
     * en ligne (CommandePreparationService), pour que le client reçoive le
     * même niveau de précision quel que soit son mode de paiement. Ajouté
     * suite au retour utilisateur du 01/09/2026 après le test "rupture
     * ciblée" du plan de test.
     */
    public static function pour(
        ?Article $article,
        int $quantiteDemandee,
        string $pointVenteAttribue,
        string $modeLivraison
    ): self {
        $designation = $article->designation ?? 'cet article';
        $messageGenerique = 'Le stock de "'.$designation.'" a été épuisé juste avant la validation de '
            .'votre commande. Merci de vérifier votre panier et réessayer.';

        if (! $article) {
            return new self($messageGenerique);
        }

        $autrePoint = $pointVenteAttribue === 'ucad' ? 'centre_ville' : 'ucad';
        $stockAutrePoint = $article->stockPour($autrePoint);
        $labels = config('pointvente.labels');

        if ($stockAutrePoint < $quantiteDemandee) {
            // Insuffisant partout : le message générique reste honnête,
            // pointer vers l'autre point n'aiderait pas puisqu'il n'en a
            // pas assez non plus.
            return new self($messageGenerique);
        }

        if ($modeLivraison === 'boutique') {
            return new self(
                'Le stock de "'.$designation.'" est épuisé au point de retrait '
                .$labels[$pointVenteAttribue].', mais disponible au point '
                .$labels[$autrePoint].' ('.$stockAutrePoint.' en stock). '
                .'Retournez à votre panier pour choisir ce point de retrait à l\'étape suivante, ou réessayez plus tard.'
            );
        }

        return new self(
            'Le stock de "'.$designation.'" est actuellement insuffisant pour une livraison à domicile, '
            .'mais disponible en boutique au point '.$labels[$autrePoint].' ('.$stockAutrePoint.' en stock). '
            .'Vous pouvez choisir un retrait en boutique à l\'étape suivante, ou réessayer plus tard.'
        );
    }
}
