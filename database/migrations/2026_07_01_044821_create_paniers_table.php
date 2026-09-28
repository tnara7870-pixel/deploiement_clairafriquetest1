<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paniers', function (Blueprint $table) {
            $table->id('idPanier');
            $table->timestamp('dateCreation')->useCurrent();
            $table->timestamp('dateModif')->nullable();
            $table->foreignId('idUtilisateur')
                ->constrained('utilisateurs', 'idUtilisateur')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paniers');
    }
};
