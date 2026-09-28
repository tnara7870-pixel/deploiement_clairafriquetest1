<?php

return [

    /*
     * Point de vente considéré comme entrepôt principal : c'est sur
     * son stock que les livraisons à domicile tapent, puisqu'elles ne
     * sont rattachées à aucun point de vente choisi par le client.
     *
     * Changer cette valeur n'affecte QUE les nouvelles commandes : les
     * commandes déjà passées gardent le point qui leur a été attribué
     * au moment de leur création (livraisons.pointVenteAttribue),
     * figé et jamais recalculé depuis cette config.
     */
    'entrepot_principal' => 'ucad',

    /*
     * Libellés affichés dans l'interface pour chaque point de vente.
     */
    'labels' => [
        'ucad' => 'UCAD',
        'centre_ville' => 'Centre-ville',
    ],

];
