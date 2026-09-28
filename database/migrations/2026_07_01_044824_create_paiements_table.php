<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id('idPaiement');
            $table->enum('modePaiement', ['wave', 'orange_money', 'especes']);
            $table->decimal('montant', 10, 2);
            $table->enum('statutPaiement', [
                'en_attente',
                'valide',
                'echoue',
                'rembourse',
            ])->default('en_attente');
            $table->string('referenceTransaction', 100)->unique()->nullable();
            $table->timestamp('datePaiement')->useCurrent();
            $table->foreignId('idCommande')
                ->unique()
                ->constrained('commandes', 'idCommande')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
