<?php

namespace App\Events;

use App\Models\Commande;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommandeConfirmee
{
    use Dispatchable, SerializesModels;

    public function __construct(public Commande $commande) {}
}
