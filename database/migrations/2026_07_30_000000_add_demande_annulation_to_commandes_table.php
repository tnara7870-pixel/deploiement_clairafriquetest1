<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // MySQL n'autorise pas d'ajouter une valeur à un ENUM via
            // Schema::table, il faut redéfinir l'ENUM complet.
            DB::statement("
                ALTER TABLE commandes
                MODIFY statut ENUM(
                    'en_attente',
                    'validee',
                    'en_livraison',
                    'livree',
                    'annulee',
                    'demande_annulation'
                ) DEFAULT 'en_attente'
            ");
        } else {
            // Branche exclusivement empruntée par la suite de tests
            // automatisés (SQLite en mémoire, voir phpunit.xml) : le projet
            // nécessite MySQL pour tourner en développement/production (voir
            // README — ENUM natif et lockForUpdate() dépendent d'InnoDB).
            // SQLite n'a pas de vrai ENUM et émule celui-ci par une
            // contrainte CHECK figée à la création de la table ; on la
            // retire ici pour permettre aux tests de fonctionner sans
            // dépendre d'une instance MySQL. La validation des valeurs
            // autorisées reste garantie côté application dans tous les cas
            // (CommandeStatutService / CommandeAnnulationService).
            Schema::table('commandes', function (Blueprint $table) {
                $table->string('statut', 30)->default('en_attente')->change();
            });
        }

        Schema::table('commandes', function (Blueprint $table) {
            // Motif saisi par le client à la demande d'annulation
            $table->text('motifAnnulation')->nullable()->after('statut');
            // Mémorise le statut avant la demande, pour pouvoir revenir en
            // arrière proprement si l'administrateur refuse la demande.
            $table->string('statutAvantAnnulation', 30)->nullable()->after('motifAnnulation');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['motifAnnulation', 'statutAvantAnnulation']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE commandes
                MODIFY statut ENUM(
                    'en_attente',
                    'validee',
                    'en_livraison',
                    'livree',
                    'annulee'
                ) DEFAULT 'en_attente'
            ");
        }
    }
};
