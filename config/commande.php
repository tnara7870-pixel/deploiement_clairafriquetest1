<?php

return [

    /*
     * Délai, en heures, au-delà duquel une commande en paiement "espèces"
     * toujours au statut "en_attente" (jamais honorée en boutique) est
     * automatiquement annulée et son stock restitué — voir
     * app/Console/Commands/ExpirerCommandesEspecesNonHonorees.php.
     *
     * Avant ce correctif, ces commandes bloquaient indéfiniment du stock
     * réel sans qu'aucune action automatique ne les résolve (voir audit
     * du 04/08/2026, point moyen 10).
     */
    'delai_expiration_especes_heures' => (int) env('COMMANDE_DELAI_EXPIRATION_ESPECES_HEURES', 48),

];
