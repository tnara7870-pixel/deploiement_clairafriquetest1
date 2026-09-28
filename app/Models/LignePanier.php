<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LignePanier extends Model
{
    protected $table = 'ligne_paniers';

    protected $primaryKey = 'idLignePanier';

    protected $fillable = [
        'quantite',
        'idPanier',
        'idArticle',
    ];

    // RELATIONS
    public function panier()
    {
        return $this->belongsTo(Panier::class, 'idPanier', 'idPanier');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'idArticle', 'idArticle');
    }

    // Sous-total de la ligne
    public function sousTotal(): float
    {
        return $this->quantite * $this->article->prix;
    }
}
