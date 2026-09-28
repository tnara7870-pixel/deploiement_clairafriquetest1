<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Compteur de connexions échouées consécutives
            $table->unsignedTinyInteger('tentativesEchouees')->default(0)->after('statut');
            // Horodatage jusqu'auquel le compte est bloqué (null = non bloqué)
            $table->timestamp('bloqueJusqua')->nullable()->after('tentativesEchouees');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn(['tentativesEchouees', 'bloqueJusqua']);
        });
    }
};
