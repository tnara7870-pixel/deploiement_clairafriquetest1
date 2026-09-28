<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeuilAlerte extends Model
{
    protected $table = 'seuils_alerte';

    protected $primaryKey = 'idSeuil';

    protected $fillable = [
        'quantiteMinimale',
        'estActif',
        'idArticle',
    ];

    protected function casts(): array
    {
        return ['estActif' => 'boolean'];
    }

    // RELATIONS
    public function article()
    {
        return $this->belongsTo(Article::class, 'idArticle', 'idArticle');
    }
}
