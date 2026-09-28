<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre Article::scopeVisible() : un article n'est visible côté client
 * que s'il est lui-même actif ET que sa catégorie l'est aussi — avant ce
 * correctif, désactiver une catégorie ne retirait rien du catalogue
 * client (voir audit du 02/08/2026).
 */
class ArticleVisibiliteTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_article_actif_dans_une_categorie_active_est_visible(): void
    {
        $categorie = Categorie::factory()->create(['statut' => true]);
        $article = Article::factory()->create(['statut' => true, 'idCategorie' => $categorie->idCategorie]);

        $this->assertTrue(Article::visible()->whereKey($article->idArticle)->exists());
    }

    public function test_un_article_inactif_nest_pas_visible(): void
    {
        $categorie = Categorie::factory()->create(['statut' => true]);
        $article = Article::factory()->create(['statut' => false, 'idCategorie' => $categorie->idCategorie]);

        $this->assertFalse(Article::visible()->whereKey($article->idArticle)->exists());
    }

    public function test_un_article_actif_dans_une_categorie_inactive_nest_pas_visible(): void
    {
        $categorie = Categorie::factory()->create(['statut' => false]);
        $article = Article::factory()->create(['statut' => true, 'idCategorie' => $categorie->idCategorie]);

        $this->assertFalse(Article::visible()->whereKey($article->idArticle)->exists());
    }

    public function test_desactiver_une_categorie_retire_ses_articles_du_catalogue_visible(): void
    {
        $categorie = Categorie::factory()->create(['statut' => true]);
        $article = Article::factory()->create(['statut' => true, 'idCategorie' => $categorie->idCategorie]);

        $this->assertTrue(Article::visible()->whereKey($article->idArticle)->exists());

        $categorie->update(['statut' => false]);

        $this->assertFalse(Article::visible()->whereKey($article->idArticle)->exists());
    }
}
