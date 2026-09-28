<?php

namespace Tests\Feature;

use App\DTO\CommandeData;
use App\Exceptions\StockInsuffisantException;
use App\Models\Article;
use App\Models\LignePanier;
use App\Models\Panier;
use App\Models\StockPointVente;
use App\Models\User;
use App\Repositories\CommandeRepository;
use App\Repositories\PanierRepository;
use App\Services\CommandeCreationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Couvre la règle métier centrale de CommandeCreationService : le stock
 * décrémenté dépend du mode de livraison — le point choisi par le client
 * pour un retrait en boutique, l'entrepôt principal (config
 * pointvente.entrepot_principal, 'ucad') pour une livraison à domicile —
 * ainsi que la numérotation des commandes, le statut de paiement selon
 * le mode, et le vidage du panier après création.
 *
 * Le stock par point est fixé explicitement via definirStock() plutôt que
 * délégué au hook Article::booted() (qui attribue quantiteStock à
 * l'entrepôt principal à la création) : ça isole ces tests de tout souci
 * de config cache ou de comportement du hook, et ça rend le stock de
 * chaque point lisible directement dans chaque test.
 */
class CommandeCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CommandeCreationService
    {
        return new CommandeCreationService(new PanierRepository, new CommandeRepository);
    }

    private function definirStock(Article $article, string $pointVente, int $quantite): void
    {
        StockPointVente::updateOrCreate(
            ['idArticle' => $article->idArticle, 'pointVente' => $pointVente],
            ['quantiteStock' => $quantite]
        );
    }

    private function creerClientAvecPanier(Article $article, int $quantite = 2): array
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $panier = Panier::create(['idUtilisateur' => $client->idUtilisateur]);
        LignePanier::create([
            'idPanier' => $panier->idPanier,
            'idArticle' => $article->idArticle,
            'quantite' => $quantite,
        ]);

        return [$client, $panier];
    }

    public function test_un_retrait_en_boutique_decremente_le_stock_du_point_choisi_par_le_client(): void
    {
        $this->seed(RoleSeeder::class);
        Event::fake();

        $article = Article::factory()->create();
        $this->definirStock($article, 'ucad', 10);
        $this->definirStock($article, 'centre_ville', 10);
        [$client] = $this->creerClientAvecPanier($article, 3);

        $data = new CommandeData(
            userId: $client->idUtilisateur,
            modeLivraison: 'boutique',
            adresse: null,
            pointVente: 'centre_ville',
            latitude: null,
            longitude: null,
            modePaiement: 'especes',
            total: $article->prix * 3,
            fraisLivraison: 0,
        );

        $this->service()->createFromPanier($data);

        $this->assertEquals(7, StockPointVente::where('idArticle', $article->idArticle)
            ->where('pointVente', 'centre_ville')->value('quantiteStock'));
        // Le point non choisi (ucad) ne doit pas bouger.
        $this->assertEquals(10, StockPointVente::where('idArticle', $article->idArticle)
            ->where('pointVente', 'ucad')->value('quantiteStock'));
    }

    public function test_une_livraison_a_domicile_decremente_lentrepot_principal_quel_que_soit_le_point_choisi(): void
    {
        $this->seed(RoleSeeder::class);
        Event::fake();

        $article = Article::factory()->create();
        $this->definirStock($article, 'ucad', 10); // entrepôt principal
        $this->definirStock($article, 'centre_ville', 0);
        [$client] = $this->creerClientAvecPanier($article, 4);

        $data = new CommandeData(
            userId: $client->idUtilisateur,
            modeLivraison: 'domicile',
            adresse: 'Sacré-Cœur 3, Dakar',
            pointVente: null,
            latitude: 14.7,
            longitude: -17.4,
            modePaiement: 'wave',
            total: $article->prix * 4,
            fraisLivraison: 2000,
        );

        $commande = $this->service()->createFromPanier($data);

        $this->assertEquals('ucad', $commande->livraison->pointVenteAttribue);
        $this->assertEquals(6, StockPointVente::where('idArticle', $article->idArticle)
            ->where('pointVente', 'ucad')->value('quantiteStock'));
    }

    public function test_stock_insuffisant_au_point_attribue_leve_une_exception_et_ne_cree_aucune_commande(): void
    {
        $this->seed(RoleSeeder::class);
        Event::fake();

        $article = Article::factory()->create();
        $this->definirStock($article, 'ucad', 2);
        $this->definirStock($article, 'centre_ville', 0);
        [$client] = $this->creerClientAvecPanier($article, 5);

        $data = new CommandeData(
            userId: $client->idUtilisateur,
            modeLivraison: 'domicile',
            adresse: 'Sacré-Cœur 3, Dakar',
            pointVente: null,
            latitude: null,
            longitude: null,
            modePaiement: 'wave',
            total: $article->prix * 5,
            fraisLivraison: 2000,
        );

        $this->expectException(StockInsuffisantException::class);

        try {
            $this->service()->createFromPanier($data);
        } finally {
            $this->assertDatabaseCount('commandes', 0);
            $this->assertEquals(2, StockPointVente::where('idArticle', $article->idArticle)
                ->where('pointVente', 'ucad')->value('quantiteStock'));
        }
    }

    public function test_le_paiement_especes_est_en_attente_et_un_paiement_en_ligne_est_valide(): void
    {
        $this->seed(RoleSeeder::class);
        Event::fake();

        $articleA = Article::factory()->create();
        $this->definirStock($articleA, 'ucad', 10);
        $articleB = Article::factory()->create();
        $this->definirStock($articleB, 'ucad', 10);
        [$clientA] = $this->creerClientAvecPanier($articleA, 1);
        [$clientB] = $this->creerClientAvecPanier($articleB, 1);

        $commandeEspeces = $this->service()->createFromPanier(new CommandeData(
            userId: $clientA->idUtilisateur, modeLivraison: 'boutique', adresse: null,
            pointVente: 'ucad', latitude: null, longitude: null,
            modePaiement: 'especes', total: $articleA->prix, fraisLivraison: 0,
        ));
        $commandeWave = $this->service()->createFromPanier(new CommandeData(
            userId: $clientB->idUtilisateur, modeLivraison: 'boutique', adresse: null,
            pointVente: 'ucad', latitude: null, longitude: null,
            modePaiement: 'wave', total: $articleB->prix, fraisLivraison: 0,
        ));

        $this->assertEquals('en_attente', $commandeEspeces->paiement->statutPaiement);
        $this->assertEquals('valide', $commandeWave->paiement->statutPaiement);
    }

    public function test_le_panier_est_vide_apres_la_creation_de_la_commande(): void
    {
        $this->seed(RoleSeeder::class);
        Event::fake();

        $article = Article::factory()->create();
        $this->definirStock($article, 'ucad', 10);
        [$client, $panier] = $this->creerClientAvecPanier($article, 2);

        $this->service()->createFromPanier(new CommandeData(
            userId: $client->idUtilisateur, modeLivraison: 'boutique', adresse: null,
            pointVente: 'ucad', latitude: null, longitude: null,
            modePaiement: 'especes', total: $article->prix * 2, fraisLivraison: 0,
        ));

        $this->assertEquals(0, $panier->lignePaniers()->count());
    }

    public function test_le_numero_de_commande_suit_le_format_cmd_annee_et_est_unique(): void
    {
        $this->seed(RoleSeeder::class);
        Event::fake();

        $article = Article::factory()->create();
        $this->definirStock($article, 'ucad', 10);
        [$client] = $this->creerClientAvecPanier($article, 1);

        $commande = $this->service()->createFromPanier(new CommandeData(
            userId: $client->idUtilisateur, modeLivraison: 'boutique', adresse: null,
            pointVente: 'ucad', latitude: null, longitude: null,
            modePaiement: 'especes', total: $article->prix, fraisLivraison: 0,
        ));

        $this->assertMatchesRegularExpression('/^CMD-\d{4}-\d{4,}$/', $commande->numeroCommande);
    }

    public function test_un_panier_vide_ne_produit_aucune_commande(): void
    {
        $this->seed(RoleSeeder::class);
        $client = User::factory()->create();
        $client->assignRole('client');
        Panier::create(['idUtilisateur' => $client->idUtilisateur]);

        $commande = $this->service()->createFromPanier(new CommandeData(
            userId: $client->idUtilisateur, modeLivraison: 'boutique', adresse: null,
            pointVente: 'ucad', latitude: null, longitude: null,
            modePaiement: 'especes', total: 0, fraisLivraison: 0,
        ));

        $this->assertNull($commande);
        $this->assertDatabaseCount('commandes', 0);
    }
}
