<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'paiements';

    protected $primaryKey = 'idPaiement';

    protected $fillable = [
        'modePaiement',
        'montant',
        'statutPaiement',
        'referenceTransaction',
        'datePaiement',
        'idCommande',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'datePaiement' => 'datetime',
        ];
    }

    // RELATIONS
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idCommande', 'idCommande');
    }
}
