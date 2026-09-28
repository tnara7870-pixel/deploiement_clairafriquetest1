<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Correction anomalie #7 (audit sécurité/paiement) : PaiementController::choix()
     * créait une nouvelle facture PayDunya à chaque appel, sans vérifier si une
     * facture précédente était déjà en attente pour le même panier. Un client
     * ouvrant deux onglets (ou relançant le paiement avant d'avoir terminé le
     * premier) pouvait générer deux factures distinctes pour le même contenu de
     * panier, toutes deux payables séparément — double débit possible pour les
     * mêmes articles.
     *
     * On persiste le token/URL de la facture PayDunya en attente directement
     * sur le panier (et non plus seulement en session), avec une expiration,
     * pour que choix() puisse détecter et réutiliser une facture déjà ouverte
     * au lieu d'en recréer une, même depuis un autre onglet ou appareil.
     */
    public function up(): void
    {
        Schema::table('paniers', function (Blueprint $table) {
            if (! Schema::hasColumn('paniers', 'paydunyaTokenEnAttente')) {
                $table->string('paydunyaTokenEnAttente', 100)->nullable()->after('idUtilisateur');
            }
            if (! Schema::hasColumn('paniers', 'paydunyaInvoiceUrlEnAttente')) {
                $table->text('paydunyaInvoiceUrlEnAttente')->nullable()->after('paydunyaTokenEnAttente');
            }
            if (! Schema::hasColumn('paniers', 'paydunyaTokenExpireA')) {
                $table->timestamp('paydunyaTokenExpireA')->nullable()->after('paydunyaInvoiceUrlEnAttente');
            }
            if (! Schema::hasColumn('paniers', 'paydunyaModePaiementEnAttente')) {
                // Mémorise le canal (wave/orange_money) avec lequel la facture
                // en attente a été créée. Sans cette information, si le client
                // revient en arrière et choisit un autre canal avant
                // l'expiration du token, on risquerait de le rediriger vers
                // l'ancienne facture PayDunya configurée pour le mauvais canal
                // au lieu d'en créer une nouvelle cohérente avec son nouveau choix.
                $table->string('paydunyaModePaiementEnAttente', 20)->nullable()->after('paydunyaTokenExpireA');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paniers', function (Blueprint $table) {
            $colonnes = array_filter(
                [
                    'paydunyaTokenEnAttente',
                    'paydunyaInvoiceUrlEnAttente',
                    'paydunyaTokenExpireA',
                    'paydunyaModePaiementEnAttente',
                ],
                fn ($c) => Schema::hasColumn('paniers', $c)
            );
            if (! empty($colonnes)) {
                $table->dropColumn($colonnes);
            }
        });
    }
};
