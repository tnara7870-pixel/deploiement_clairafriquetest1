<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id('idLivraison');
            $table->enum('modeLivraison', ['domicile', 'boutique']);
            $table->text('adresseLivraison')->nullable();
            // Refonte du 04/08/2026 : 'expediee' et 'en_cours' fusionnés en un
            // seul statut 'en_livraison'. Les deux anciens statuts se
            // mappaient de toute façon vers le même statut de commande
            // ('en_livraison'), sans jamais apporter de distinction utile
            // au client ni au personnel — juste de la confusion et un bug
            // de workflow (voir audit du 02/08/2026, point critique 2).
            // Nommer le statut de livraison à l'identique du statut de
            // commande qu'il déclenche élimine la correspondance ambiguë
            // "deux valeurs sources → une seule valeur cible" qui causait
            // le bug.
            $table->enum('statutLivraison', [
                'preparee',
                'en_livraison',
                'livree',
                'annulee',
            ])->default('preparee');
            $table->timestamp('dateLivraison')->nullable();
            $table->foreignId('idCommande')
                ->unique()
                ->constrained('commandes', 'idCommande')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};
