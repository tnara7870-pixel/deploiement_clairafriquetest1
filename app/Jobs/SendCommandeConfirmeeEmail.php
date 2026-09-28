<?php

namespace App\Jobs;

use App\Mail\CommandeConfirmee;
use App\Models\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCommandeConfirmeeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Commande $commande) {}

    public function handle(): void
    {
        if (! $this->commande->utilisateur?->email) {
            return;
        }

        Mail::to($this->commande->utilisateur->email)
            ->send(new CommandeConfirmee($this->commande));
    }
}
