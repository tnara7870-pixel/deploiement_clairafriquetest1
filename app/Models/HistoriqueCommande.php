<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueCommande extends Model
{
    protected $table = 'historique_commandes';

    protected $primaryKey = 'idHistorique';

    public $timestamps = false;

    protected $fillable = [
        'idCommande',
        'statutPrecedent',
        'statutNouveau',
        'action',
        'idUtilisateur',
        'commentaire',
        'dateAction',
    ];

    protected function casts(): array
    {
        return ['dateAction' => 'datetime'];
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idCommande', 'idCommande');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur', 'idUtilisateur');
    }
}
