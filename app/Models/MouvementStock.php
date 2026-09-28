<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';

    protected $primaryKey = 'idMouvement';

    protected $fillable = [
        'typeMouvement',
        'quantite',
        'dateMouvement',
        'motif',
        'idArticle',
        'pointVente',
        'idUtilisateur',
        'idCommande',
    ];

    protected function casts(): array
    {
        return ['dateMouvement' => 'datetime'];
    }

    // RELATIONS
    public function article()
    {
        return $this->belongsTo(Article::class, 'idArticle', 'idArticle');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idCommande', 'idCommande');
    }
}
