<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sépare le stock par point de vente (UCAD / Centre-ville) tout en
 * gardant un catalogue d'articles UNIQUE et partagé : c'est la même
 * fiche article, mais deux compteurs de quantité indépendants.
 *
 * articles.quantiteStock N'EST PAS supprimée : elle reste le TOTAL
 * (somme des deux points), tenue à jour à chaque mouvement, pour ne
 * pas casser tout le code existant qui l'utilise déjà comme total
 * global (catalogue client, panier, alertes globales, exports...).
 * Cette table-ci est la source de vérité pour la répartition par
 * point ; articles.quantiteStock en est une valeur dérivée mise en
 * cache.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks_points_vente', function (Blueprint $table) {
            $table->id('idStockPointVente');
            $table->foreignId('idArticle')
                ->constrained('articles', 'idArticle')
                ->onDelete('cascade');
            $table->enum('pointVente', ['ucad', 'centre_ville']);
            $table->unsignedInteger('quantiteStock')->default(0);
            $table->timestamps();

            $table->unique(['idArticle', 'pointVente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks_points_vente');
    }
};
