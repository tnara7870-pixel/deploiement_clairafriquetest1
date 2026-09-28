<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration standard de Laravel (queue:table / queue:failed-table),
 * absente du projet alors que App\Jobs\SendCommandeConfirmeeEmail
 * implémente ShouldQueue. Tant que QUEUE_CONNECTION=sync (voir
 * config/queue.php), cette table n'est pas utilisée — mais si jamais
 * 'database' est choisi (explicitement, ou par défaut si la variable
 * d'environnement est absente d'un .env de production), son absence
 * provoque une SQLSTATE[42S02] qui remonte jusqu'à la création de
 * commande elle-même. Constaté en conditions réelles le 05/08/2026 :
 * une commande déjà payée et enregistrée en base était signalée comme
 * "échouée" au client à cause de cette seule table manquante. Voir
 * CommandeCreationService::createFromPanier() et
 * PaiementDomainService::confirmerInvoice(), désormais protégés
 * indépendamment de ce correctif (l'échec d'une notification ne peut
 * plus jamais invalider une commande déjà actée), mais cette table
 * reste nécessaire pour que la notification elle-même parte un jour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
