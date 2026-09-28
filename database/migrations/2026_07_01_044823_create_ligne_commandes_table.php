<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_commandes', function (Blueprint $table) {
            $table->id('idLigneCommande');
            $table->integer('quantite');
            $table->decimal('prixUnitaire', 10, 2);
            $table->foreignId('idCommande')
                ->constrained('commandes', 'idCommande')
                ->onDelete('cascade');
            $table->foreignId('idArticle')
                ->constrained('articles', 'idArticle')
                ->onDelete('restrict');
            $table->timestamps();
            $table->unique(['idCommande', 'idArticle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_commandes');
    }
};
