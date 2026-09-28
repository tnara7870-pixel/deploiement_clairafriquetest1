<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remboursement extends Model
{
    protected $table = 'remboursements';

    protected $primaryKey = 'idRemboursement';

    protected $fillable = [
        'idCommande',
        'idPaiement',
        'referenceTransaction',
        'dateRemboursement',
        'montant',
        'commentaire',
        'idUtilisateur',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'dateRemboursement' => 'date',
        ];
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idCommande', 'idCommande');
    }

    public function paiement()
    {
        return $this->belongsTo(Paiement::class, 'idPaiement', 'idPaiement');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur', 'idUtilisateur');
    }
}
