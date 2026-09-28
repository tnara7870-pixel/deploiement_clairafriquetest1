<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trois colonnes additives, toutes nullables (aucune donnée existante
 * cassée, aucun rôle dupliqué) :
 *
 * - utilisateurs.pointVenteAssigne : le point de vente d'un res.stock
 *   ou res.commande. NULL = accès global (comportement actuel
 *   inchangé pour les comptes déjà existants et pour un
 *   administrateur).
 *
 * - livraisons.pointVenteAttribue : LE point de vente qui gère
 *   réellement cette commande de bout en bout — celui dont le stock a
 *   été décrémenté ET celui dont les responsables (stock/commande)
 *   doivent la voir dans leurs listes. Pour un retrait en boutique,
 *   c'est le point choisi par le client (déjà stocké dans
 *   `pointVente`) ; pour une livraison à domicile (qui n'est
 *   rattachée à aucun point côté client), c'est l'entrepôt principal
 *   configuré (config('pointvente.entrepot_principal')), figé au
 *   moment de la commande pour ne jamais changer rétroactivement même
 *   si la config change plus tard.
 *
 * - mouvements_stock.pointVente : quel point de vente est affecté par
 *   ce mouvement. Nullable car les mouvements créés avant cette
 *   fonctionnalité n'en ont pas — ils restent visibles uniquement
 *   pour les comptes non scopés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->enum('pointVenteAssigne', ['ucad', 'centre_ville'])
                ->nullable()
                ->after('idUtilisateur');
        });

        Schema::table('livraisons', function (Blueprint $table) {
            $table->enum('pointVenteAttribue', ['ucad', 'centre_ville'])
                ->nullable()
                ->after('pointVente');
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->enum('pointVente', ['ucad', 'centre_ville'])
                ->nullable()
                ->after('idArticle');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn('pointVenteAssigne');
        });

        Schema::table('livraisons', function (Blueprint $table) {
            $table->dropColumn('pointVenteAttribue');
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->dropColumn('pointVente');
        });
    }
};
