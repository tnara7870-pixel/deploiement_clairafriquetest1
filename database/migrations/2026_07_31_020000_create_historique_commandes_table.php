<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historique horodaté de chaque changement de statut / décision sur
     * une commande (qui, quoi, quand, pourquoi). Table dédiée plutôt que
     * de réutiliser mouvements_stock (qui ne concerne que le stock) —
     * voir refactorisation du workflow d'annulation du 31/07/2026.
     */
    public function up(): void
    {
        if (Schema::hasTable('historique_commandes')) {
            return;
        }

        Schema::create('historique_commandes', function (Blueprint $table) {
            $table->id('idHistorique');
            $table->foreignId('idCommande')
                ->constrained('commandes', 'idCommande')
                ->onDelete('cascade');
            $table->string('statutPrecedent', 30)->nullable();
            $table->string('statutNouveau', 30);
            $table->enum('action', [
                'creation',
                'changement_statut',
                'demande_annulation',
                'annulation_validee',
                'annulation_refusee',
                'remboursement_enregistre',
            ]);
            $table->foreignId('idUtilisateur')
                ->nullable()
                ->constrained('utilisateurs', 'idUtilisateur')
                ->onDelete('set null');
            $table->text('commentaire')->nullable();
            $table->timestamp('dateAction')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_commandes');
    }
};
