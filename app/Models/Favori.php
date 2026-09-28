<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favori extends Model
{
    protected $table = 'favoris';

    protected $primaryKey = 'idFavori';

    protected $fillable = [
        'idUtilisateur',
        'idArticle',
    ];

    // RELATIONS
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'idArticle', 'idArticle');
    }
}
