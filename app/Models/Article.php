<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    protected $primaryKey = 'idArticle';

    protected $fillable = [
        'reference',
        'designation',
        'description',
        'prix',
        'quantiteStock',
        'image',
        'statut',
        'idCategorie',
    ];

    protected function casts(): array
    {
        return [
            'statut' => 'boolean',
            'prix' => 'decimal:2',
        ];
    }

    /**
     * Garantit qu'AUCUN article ne peut exister sans ses deux lignes de
     * stock par point de vente, quel que soit ce qui le crée — le
     * formulaire admin (ArticleService), une factory de test, un seeder
     * de démo, ou du code futur qui appellerait Article::create()
     * directement. Avant ce hook, seul ArticleService les créait : un
     * article né ailleurs (ArticleFactory, TestSeeder — cas réels
     * trouvés à l'audit du 31/08/2026) se retrouvait avec quantiteStock
     * renseigné mais stockPour('ucad')/stockPour('centre_ville') à 0,
     * ce qui bloquait à tort toute commande dessus et faussait le total
     * à la première resynchronisation (annulation, mouvement...).
     * firstOrCreate() la rend sûre même si ArticleService en a déjà créé
     * une explicitement.
     */
    protected static function booted(): void
    {
        static::created(function (Article $article) {
            foreach (array_keys(config('pointvente.labels')) as $point) {
                StockPointVente::firstOrCreate([
                    'idArticle' => $article->idArticle,
                    'pointVente' => $point,
                ], [
                    'quantiteStock' => 0,
                ]);
            }

            // Si l'appelant a fourni un quantiteStock non nul dès la
            // création (factory de test, seeder de démo, ou tout code qui
            // n'utilise pas ArticleService::createArticle()), on l'attribue
            // à l'entrepôt principal plutôt que de le laisser orphelin sur
            // aucun point — même politique que la migration de bascule du
            // stock déjà existant. ArticleService, lui, crée toujours
            // l'article à quantiteStock=0 puis gère la répartition lui-même
            // via son paramètre $pointVenteInitial : ce bloc ne s'applique
            // donc jamais à son flux normal, seulement aux contournements.
            if ($article->quantiteStock > 0) {
                StockPointVente::where('idArticle', $article->idArticle)
                    ->where('pointVente', config('pointvente.entrepot_principal'))
                    ->increment('quantiteStock', $article->quantiteStock);
            }
        });
    }

    // RELATIONS
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'idCategorie', 'idCategorie');
    }

    public function lignePaniers()
    {
        return $this->hasMany(LignePanier::class, 'idArticle', 'idArticle');
    }

    public function ligneCommandes()
    {
        return $this->hasMany(LigneCommande::class, 'idArticle', 'idArticle');
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class, 'idArticle', 'idArticle');
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class, 'idArticle', 'idArticle');
    }

    public function seuilAlerte()
    {
        return $this->hasOne(SeuilAlerte::class, 'idArticle', 'idArticle');
    }

    public function stocksPointVente()
    {
        return $this->hasMany(StockPointVente::class, 'idArticle', 'idArticle');
    }

    /**
     * Quantité disponible pour UN point de vente donné. Utilisé partout
     * où l'on manipule ou vérifie le stock d'un point précis (mouvements,
     * décrément à la commande, dashboards scopés). Ne pas confondre avec
     * quantiteStock, qui reste le TOTAL des deux points.
     */
    public function stockPour(string $pointVente): int
    {
        return $this->stocksPointVente->firstWhere('pointVente', $pointVente)?->quantiteStock ?? 0;
    }

    /**
     * Recalcule et persiste quantiteStock comme somme des deux points,
     * pour que le total reste toujours exact après toute opération sur
     * stocks_points_vente. À appeler dans la même transaction que la
     * modification du stock par point.
     */
    public function resynchroniserQuantiteTotale(): void
    {
        $this->update([
            'quantiteStock' => $this->stocksPointVente()->sum('quantiteStock'),
        ]);
    }

    /**
     * Articles réellement visibles/achetables côté client : l'article
     * doit être actif ET sa catégorie doit l'être aussi. Auparavant, le
     * statut d'une catégorie n'avait aucun effet réel sur ses articles
     * (Client\CatalogueController ne vérifiait que $article->statut) :
     * désactiver une catégorie depuis le back-office ne retirait rien du
     * catalogue client, y compris en accès direct via l'URL d'un article.
     * Voir audit du 02/08/2026.
     */
    public function scopeVisible($query)
    {
        return $query->where('statut', true)
            ->whereHas('categorie', fn ($q) => $q->where('statut', true));
    }

    // Vérifie si le stock est sous le seuil d'alerte
    public function estEnAlerte(): bool
    {
        if ($this->seuilAlerte && $this->seuilAlerte->estActif) {
            return $this->quantiteStock <= $this->seuilAlerte->quantiteMinimale;
        }

        return false;
    }
}
