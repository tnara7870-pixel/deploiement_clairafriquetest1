<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vérification d'adresse email à l'inscription par code à usage unique.
 *
 * Avant cette migration, un client pouvait s'inscrire avec n'importe
 * quelle adresse email sans qu'elle soit jamais confirmée : un email mal
 * saisi ou appartenant à un tiers restait indétectable, alors que cette
 * adresse sert ensuite à la confirmation de commande et à la
 * réinitialisation de mot de passe.
 *
 * - emailVerifieLe        : date de confirmation effective (NULL = non
 *                            vérifié).
 * - codeVerification      : hash bcrypt du code à 6 chiffres envoyé par
 *                            email (jamais stocké en clair, comme pour
 *                            motDePasse).
 * - codeVerificationExpire: expiration du code (15 minutes), pour éviter
 *                            qu'un code intercepté reste valable
 *                            indéfiniment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->timestamp('emailVerifieLe')->nullable()->after('email');
            $table->string('codeVerification')->nullable()->after('emailVerifieLe');
            $table->timestamp('codeVerificationExpire')->nullable()->after('codeVerification');
        });

        // Comptes déjà existants (créés avant l'ajout de cette
        // fonctionnalité) : considérés comme vérifiés d'office, sinon ils
        // seraient bloqués au prochain login sans jamais avoir reçu de
        // code. Seules les inscriptions à venir passeront par le flux de
        // vérification.
        DB::table('utilisateurs')
            ->whereNull('emailVerifieLe')
            ->update(['emailVerifieLe' => DB::raw('dateCreation')]);
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn(['emailVerifieLe', 'codeVerification', 'codeVerificationExpire']);
        });
    }
};
