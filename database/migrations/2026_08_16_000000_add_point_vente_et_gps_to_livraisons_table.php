<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livraisons', function (Blueprint $table) {
            // Pertinent uniquement quand modeLivraison = 'boutique' — reste
            // nullable pour les livraisons à domicile, où la question ne se
            // pose pas. Deux points de vente actuellement : UCAD et
            // Centre-ville.
            $table->enum('pointVente', ['ucad', 'centre_ville'])
                ->nullable()
                ->after('modeLivraison');

            // Coordonnées GPS optionnelles pour une livraison à domicile,
            // renseignées via géolocalisation navigateur + carte
            // interactive (voir recapitulatif.blade.php). Le champ texte
            // adresseLivraison reste la source de vérité affichée au
            // livreur — les coordonnées sont un complément, jamais un
            // remplacement, car l'adressage informel de nombreux quartiers
            // rend un point GPS seul insuffisant pour livrer.
            $table->decimal('latitude', 10, 7)->nullable()->after('adresseLivraison');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('livraisons', function (Blueprint $table) {
            $table->dropColumn(['pointVente', 'latitude', 'longitude']);
        });
    }
};
