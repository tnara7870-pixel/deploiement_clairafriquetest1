<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favoris', function (Blueprint $table) {
            $table->id('idFavori');
            $table->timestamp('dateAjout')->useCurrent();
            $table->foreignId('idUtilisateur')
                ->constrained('utilisateurs', 'idUtilisateur')
                ->onDelete('cascade');
            $table->foreignId('idArticle')
                ->constrained('articles', 'idArticle')
                ->onDelete('cascade');
            $table->timestamps();
            $table->unique(['idUtilisateur', 'idArticle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};
