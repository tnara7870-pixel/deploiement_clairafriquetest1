<?php

namespace App\Mail;

use App\Models\Commande;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email de confirmation envoyé au client juste après la création réussie
 * d'une commande (paiement PayDunya confirmé ou commande espèces
 * enregistrée), facture PDF jointe. Auparavant, aucun email n'était
 * envoyé nulle part dans le projet (seules les notifications in-app
 * existaient) — un client changeant d'appareil ou fermant l'onglet
 * n'avait plus aucune trace écrite de son achat. Voir audit du
 * 02/08/2026.
 */
class CommandeConfirmee extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Commande $commande) {}

    public function build(): self
    {
        $commande = $this->commande->loadMissing(['ligneCommandes.article', 'utilisateur', 'livraison', 'paiement']);

        $pdf = Pdf::loadView('pdf.facture', ['commande' => $commande]);
        $pdf->setPaper('A4', 'portrait');

        return $this->subject('Confirmation de votre commande '.$commande->numeroCommande)
            ->view('mail.commande-confirmee')
            ->with(['commande' => $commande])
            ->attachData($pdf->output(), 'Facture-'.$commande->numeroCommande.'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
