<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    protected $table = 'livraisons';

    protected $primaryKey = 'idLivraison';

    protected $fillable = [
        'modeLivraison',
        'pointVente',
        'pointVenteAttribue',
        'adresseLivraison',
        'latitude',
        'longitude',
        'statutLivraison',
        'dateLivraison',
        'idCommande',
    ];

    protected function casts(): array
    {
        // dateLivraison n'était pas casté : aucun crash actuel (non
        // affiché avec ->format() nulle part dans le projet), mais même
        // défaut que celui trouvé sur Commande::dateCommande — corrigé
        // par prévention avant qu'un futur ->format() direct ne plante.
        return ['dateLivraison' => 'datetime'];
    }

    // RELATIONS
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idCommande', 'idCommande');
    }
}
