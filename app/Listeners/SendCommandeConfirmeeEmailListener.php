<?php

namespace App\Listeners;

use App\Events\CommandeConfirmee;
use App\Jobs\SendCommandeConfirmeeEmail;

class SendCommandeConfirmeeEmailListener
{
    public function handle(CommandeConfirmee $event): void
    {
        SendCommandeConfirmeeEmail::dispatch($event->commande);
    }
}
