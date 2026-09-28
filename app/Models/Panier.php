<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    protected $table = 'paniers';

    protected $primaryKey = 'idPanier';

    protected $fillable = [
        'idUtilisateur',
        'dateModif',
        'paydunyaTokenEnAttente',
        'paydunyaInvoiceUrlEnAttente',
        'paydunyaTokenExpireA',
        'paydunyaModePaiementEnAttente',
    ];

    protected function casts(): array
    {
        return [
            'paydunyaTokenExpireA' => 'datetime',
        ];
    }

    // RELATIONS
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function lignePaniers()
    {
        return $this->hasMany(LignePanier::class, 'idPanier', 'idPanier');
    }

    public function articles()
    {
        return $this->belongsToMany(
            Article::class,
            'ligne_paniers',
            'idPanier',
            'idArticle',
            'idPanier',
            'idArticle'
        )->withPivot('quantite')->withTimestamps();
    }

    // Calcule le montant total du panier
    public function calculerMontant(): float
    {
        return $this->lignePaniers->sum(function ($ligne) {
            return $ligne->quantite * $ligne->article->prix;
        });
    }
}
