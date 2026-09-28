<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneCommande extends Model
{
    protected $table = 'ligne_commandes';

    protected $primaryKey = 'idLigneCommande';

    protected $fillable = [
        'quantite',
        'prixUnitaire',
        'idCommande',
        'idArticle',
    ];

    protected function casts(): array
    {
        return ['prixUnitaire' => 'decimal:2'];
    }

    // RELATIONS
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idCommande', 'idCommande');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'idArticle', 'idArticle');
    }

    // Sous-total figé au moment de la commande
    public function sousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }
}
