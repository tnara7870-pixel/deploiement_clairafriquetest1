<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traçabilité du consentement aux conditions d'utilisation et à la
 * politique de confidentialité (traitement des données personnelles),
 * conformément à la loi sénégalaise n°2008-12 du 25 janvier 2008 relative
 * à la protection des données à caractère personnel : le responsable de
 * traitement doit pouvoir démontrer que la personne concernée a
 * consenti (principe de redevabilité / preuve du consentement).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->timestamp('conditionsAccepteesLe')->nullable()->after('emailVerifieLe');
        });

        // Comptes déjà existants : la case n'existait pas encore lors de
        // leur inscription, donc pas de date de consentement à inventer ;
        // on les laisse NULL plutôt que de fabriquer une preuve fictive.
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn('conditionsAccepteesLe');
        });
    }
};
