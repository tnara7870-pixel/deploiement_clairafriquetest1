<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id('idCommande');
            $table->string('numeroCommande', 30)->unique();
            $table->timestamp('dateCommande')->useCurrent();
            $table->decimal('montantTotal', 10, 2)->default(0);
            $table->enum('statut', [
                'en_attente',
                'validee',
                'en_livraison',
                'livree',
                'annulee',
            ])->default('en_attente');
            $table->foreignId('idUtilisateur')
                ->constrained('utilisateurs', 'idUtilisateur')
                ->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
