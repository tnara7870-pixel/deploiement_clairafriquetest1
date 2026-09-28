<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Libère le stock des commandes "espèces" jamais honorées en boutique
// (voir App\Console\Commands\ExpirerCommandesEspecesNonHonorees et
// config/commande.php pour le délai). Horaire plutôt que quotidien : le
// stock bloqué inutilement est un manque à gagner direct, autant le
// libérer rapidement une fois le délai dépassé plutôt qu'attendre le
// lendemain. withoutOverlapping() protège contre un run qui durerait
// plus d'une heure sur un très gros volume de commandes.
Schedule::command('commandes:expirer-especes')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
