<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enregistrement du remboursement manuel effectué par l'administrateur
     * depuis le tableau de bord PayDunya (le prestataire ne proposant pas
     * de remboursement automatique via API). Un formulaire — et non une
     * simple case à cocher — pour garder une preuve exploitable :
     * référence de transaction, date, montant, commentaire éventuel.
     */
    public function up(): void
    {
        if (Schema::hasTable('remboursements')) {
            return;
        }

        Schema::create('remboursements', function (Blueprint $table) {
            $table->id('idRemboursement');
            $table->foreignId('idCommande')
                ->constrained('commandes', 'idCommande')
                ->onDelete('cascade');
            $table->foreignId('idPaiement')
                ->nullable()
                ->constrained('paiements', 'idPaiement')
                ->onDelete('set null');
            $table->string('referenceTransaction', 100);
            $table->date('dateRemboursement');
            $table->decimal('montant', 10, 2);
            $table->text('commentaire')->nullable();
            $table->foreignId('idUtilisateur')
                ->constrained('utilisateurs', 'idUtilisateur')
                ->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remboursements');
    }
};
