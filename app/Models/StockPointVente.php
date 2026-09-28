<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPointVente extends Model
{
    protected $table = 'stocks_points_vente';

    protected $primaryKey = 'idStockPointVente';

    protected $fillable = [
        'idArticle',
        'pointVente',
        'quantiteStock',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class, 'idArticle', 'idArticle');
    }
}
