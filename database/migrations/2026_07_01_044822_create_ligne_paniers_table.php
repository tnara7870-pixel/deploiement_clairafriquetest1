<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_paniers', function (Blueprint $table) {
            $table->id('idLignePanier');
            $table->integer('quantite')->default(1);
            $table->foreignId('idPanier')
                ->constrained('paniers', 'idPanier')
                ->onDelete('cascade');
            $table->foreignId('idArticle')
                ->constrained('articles', 'idArticle')
                ->onDelete('restrict');
            $table->timestamps();
            // Un article ne peut apparaître qu'une fois par panier
            $table->unique(['idPanier', 'idArticle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_paniers');
    }
};
