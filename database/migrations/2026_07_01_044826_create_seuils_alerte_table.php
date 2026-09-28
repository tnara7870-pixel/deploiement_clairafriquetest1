<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seuils_alerte', function (Blueprint $table) {
            $table->id('idSeuil');
            $table->integer('quantiteMinimale')->default(0);
            $table->boolean('estActif')->default(true);
            $table->foreignId('idArticle')
                ->unique()
                ->constrained('articles', 'idArticle')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seuils_alerte');
    }
};
