<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute un signalement visible quand une commande a été payée
     * (PayDunya) mais qu'un ou plusieurs articles n'avaient plus assez de
     * stock au moment de la confirmation. Auparavant, ce cas ne laissait
     * de trace que dans les logs serveur — invisible pour le personnel.
     * Voir audit du 31/07/2026.
     */
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->boolean('problemeStock')->default(false)->after('statut');
            $table->text('problemeStockDetails')->nullable()->after('problemeStock');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['problemeStock', 'problemeStockDetails']);
        });
    }
};
