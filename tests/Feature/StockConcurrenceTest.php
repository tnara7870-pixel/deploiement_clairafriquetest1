<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie que le mécanisme anti-survente (Article::lockForUpdate() +
 * vérification de la quantité disponible avant décrément, dans
 * Stock\MouvementController::store()) refuse bien une sortie de stock
 * supérieure à ce qui est disponible, et que le stock ne passe jamais
 * en négatif.
 *
 * Un vrai test de concurrence nécessiterait deux connexions DB
 * parallèles réelles (hors de portée d'un test SQLite en mémoire
 * mono-processus) ; on teste ici la garde métier elle-même en
 * enchaînant deux sorties qui, prises ensemble, dépassent le stock
 * disponible — ce qui est le scénario que le verrou est censé empêcher
 * en production sous charge concurrente.
 */
class StockConcurrenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_sortie_de_stock_superieure_au_disponible_est_refusee(): void
    {
        $this->seed(RoleSeeder::class);
        $stockUser = User::factory()->create();
        $stockUser->assignRole('res.stock');

        $article = Article::factory()->create(['quantiteStock' => 5]);

        // Première sortie : dans la limite du stock disponible → acceptée.
        // pointVente requis car $stockUser n'est pas scopé à un point
        // (accès global) — voir Stock\MouvementController::store().
        $this->actingAs($stockUser)->post(route('stock.mouvement.store'), [
            'typeMouvement' => 'sortie',
            'idArticle' => $article->idArticle,
            'pointVente' => 'ucad',
            'quantite' => 5,
            'motif' => 'Vente 1',
        ])->assertSessionHas('success');

        $this->assertEquals(0, $article->fresh()->quantiteStock);

        // Deuxième sortie sur un stock désormais épuisé → doit être refusée,
        // et le stock ne doit surtout pas passer en négatif.
        $this->actingAs($stockUser)->post(route('stock.mouvement.store'), [
            'typeMouvement' => 'sortie',
            'idArticle' => $article->idArticle,
            'pointVente' => 'ucad',
            'quantite' => 1,
            'motif' => 'Vente 2',
        ])->assertSessionHas('error');

        $this->assertEquals(0, $article->fresh()->quantiteStock);
        $this->assertDatabaseCount('mouvements_stock', 1);
    }

    public function test_le_stock_ne_descend_jamais_en_dessous_de_zero(): void
    {
        $this->seed(RoleSeeder::class);
        $stockUser = User::factory()->create();
        $stockUser->assignRole('res.stock');

        $article = Article::factory()->create(['quantiteStock' => 2]);

        $this->actingAs($stockUser)->post(route('stock.mouvement.store'), [
            'typeMouvement' => 'sortie',
            'idArticle' => $article->idArticle,
            'pointVente' => 'ucad',
            'quantite' => 10,
            'motif' => 'Tentative de survente',
        ])->assertSessionHas('error');

        $this->assertEquals(2, $article->fresh()->quantiteStock);
    }
}
