<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id('idArticle');
            $table->string('reference', 20)->unique();
            $table->string('designation', 150);
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2);
            $table->unsignedInteger('quantiteStock')->default(0);
            $table->string('image', 255)->nullable();
            $table->boolean('statut')->default(true);
            $table->foreignId('idCategorie')
                ->constrained('categories', 'idCategorie')
                ->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
