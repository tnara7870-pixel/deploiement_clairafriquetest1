<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\LignePanier;
use App\Models\Panier;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Couvre la protection contre l'IDOR sur les lignes de panier (un client
 * ne doit pouvoir modifier/supprimer que ses propres lignes — voir
 * PanierController::modifierLigne()/supprimer()) et le respect de la
 * limite de stock à l'ajout et à la modification de quantité.
 */
class PanierTest extends TestCase
{
    use RefreshDatabase;

    private function creerClient(): User
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        // Si la vérification d'email par code (colonne emailVerifieLe +
        // middleware email.verified sur ce groupe de routes) a été
        // fusionnée dans ce dépôt, un client tout juste créé par la
        // factory serait redirigé vers /verification-email avant même
        // d'atteindre PanierController — sans rapport avec ce que ces
        // tests vérifient. On le marque donc déjà vérifié quand la
        // colonne existe ; ne fait rien sinon.
        if (Schema::hasColumn('utilisateurs', 'emailVerifieLe')) {
            $client->forceFill(['emailVerifieLe' => now()])->save();
        }

        return $client;
    }

    public function test_un_client_ne_peut_pas_modifier_la_ligne_de_panier_dun_autre_client(): void
    {
        $this->seed(RoleSeeder::class);
        $proprietaire = $this->creerClient();
        $intrus = $this->creerClient();
        $article = Article::factory()->create(['quantiteStock' => 10]);

        $panier = Panier::create(['idUtilisateur' => $proprietaire->idUtilisateur]);
        $ligne = LignePanier::create([
            'idPanier' => $panier->idPanier,
            'idArticle' => $article->idArticle,
            'quantite' => 2,
        ]);

        $this->actingAs($intrus)
            ->patch(route('client.panier.modifier', $ligne->idLignePanier), ['quantite' => 5])
            ->assertNotFound();

        $this->assertEquals(2, $ligne->fresh()->quantite);
    }

    public function test_un_client_ne_peut_pas_supprimer_la_ligne_de_panier_dun_autre_client(): void
    {
        $this->seed(RoleSeeder::class);
        $proprietaire = $this->creerClient();
        $intrus = $this->creerClient();
        $article = Article::factory()->create(['quantiteStock' => 10]);

        $panier = Panier::create(['idUtilisateur' => $proprietaire->idUtilisateur]);
        $ligne = LignePanier::create([
            'idPanier' => $panier->idPanier,
            'idArticle' => $article->idArticle,
            'quantite' => 2,
        ]);

        $this->actingAs($intrus)
            ->delete(route('client.panier.supprimer', $ligne->idLignePanier))
            ->assertNotFound();

        $this->assertDatabaseHas('ligne_paniers', ['idLignePanier' => $ligne->idLignePanier]);
    }

    public function test_le_proprietaire_peut_modifier_sa_propre_ligne(): void
    {
        $this->seed(RoleSeeder::class);
        $client = $this->creerClient();
        $article = Article::factory()->create(['quantiteStock' => 10]);

        $panier = Panier::create(['idUtilisateur' => $client->idUtilisateur]);
        $ligne = LignePanier::create([
            'idPanier' => $panier->idPanier,
            'idArticle' => $article->idArticle,
            'quantite' => 2,
        ]);

        $this->actingAs($client)
            ->patch(route('client.panier.modifier', $ligne->idLignePanier), ['quantite' => 5])
            ->assertRedirect();

        $this->assertEquals(5, $ligne->fresh()->quantite);
    }

    public function test_impossible_dajouter_au_panier_plus_que_le_stock_disponible(): void
    {
        $this->seed(RoleSeeder::class);
        $client = $this->creerClient();
        $article = Article::factory()->create(['quantiteStock' => 3]);

        $this->actingAs($client)->post(route('client.panier.ajouter'), [
            'idArticle' => $article->idArticle,
            'quantite' => 4,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('ligne_paniers', ['idArticle' => $article->idArticle]);
    }

    public function test_ajouter_deux_fois_le_meme_article_cumule_la_quantite_sans_depasser_le_stock(): void
    {
        $this->seed(RoleSeeder::class);
        $client = $this->creerClient();
        $article = Article::factory()->create(['quantiteStock' => 5]);

        $this->actingAs($client)->post(route('client.panier.ajouter'), [
            'idArticle' => $article->idArticle, 'quantite' => 3,
        ]);
        $this->actingAs($client)->post(route('client.panier.ajouter'), [
            'idArticle' => $article->idArticle, 'quantite' => 3,
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('ligne_paniers', ['idArticle' => $article->idArticle, 'quantite' => 3]);
    }
}
