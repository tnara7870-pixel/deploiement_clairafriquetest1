<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la valeur "probleme_stock_resolu" à l'enum "action" de
     * historique_commandes, pour tracer le traitement d'un signalement de
     * rupture de stock (auparavant, resoudreProblemeStock() effaçait
     * problemeStockDetails sans laisser aucune trace — incohérent avec le
     * reste du projet où chaque changement d'état est historisé). Voir
     * audit du 02/08/2026, point moyen 7.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE historique_commandes
                MODIFY action ENUM(
                    'creation',
                    'changement_statut',
                    'demande_annulation',
                    'annulation_validee',
                    'annulation_refusee',
                    'remboursement_enregistre',
                    'probleme_stock_resolu'
                ) NOT NULL
            ");
        } else {
            // SQLite (tests) : même logique que pour commandes.statut, voir
            // 2026_07_30_000000_add_demande_annulation_to_commandes_table.
            Schema::table('historique_commandes', function (Blueprint $table) {
                $table->string('action', 30)->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE historique_commandes
                MODIFY action ENUM(
                    'creation',
                    'changement_statut',
                    'demande_annulation',
                    'annulation_validee',
                    'annulation_refusee',
                    'remboursement_enregistre'
                ) NOT NULL
            ");
        }
    }
};
