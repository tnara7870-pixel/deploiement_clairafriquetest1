<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'idCategorie';

    protected $fillable = [
        'nomCategorie',
        'description',
        'statut',
    ];

    protected function casts(): array
    {
        return ['statut' => 'boolean'];
    }

    // RELATIONS
    public function articles()
    {
        return $this->hasMany(Article::class, 'idCategorie', 'idCategorie');
    }
}
