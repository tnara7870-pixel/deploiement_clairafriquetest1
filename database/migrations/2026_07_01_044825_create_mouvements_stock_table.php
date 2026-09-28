<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id('idMouvement');
            $table->enum('typeMouvement', [
                'entree',
                'sortie',
                'retour',
                'ajustement',
            ]);
            $table->integer('quantite');
            $table->timestamp('dateMouvement')->useCurrent();
            $table->string('motif', 150)->nullable();
            $table->foreignId('idArticle')
                ->constrained('articles', 'idArticle')
                ->onDelete('restrict');
            // Nullable : un mouvement peut être déclenché automatiquement
            // par le système (ex. libération de stock à l'expiration d'une
            // commande "espèces" jamais honorée, voir
            // ExpirerCommandesEspecesNonHonorees) et non par une action
            // humaine identifiable. Cohérent avec historique_commandes.idUtilisateur,
            // déjà nullable pour la même raison.
            $table->foreignId('idUtilisateur')
                ->nullable()
                ->constrained('utilisateurs', 'idUtilisateur')
                ->onDelete('set null');
            $table->foreignId('idCommande')
                ->nullable()
                ->constrained('commandes', 'idCommande')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
    }
};
