<?php

namespace Tests\Feature;

use App\Exceptions\CommandeAnnulationException;
use App\Models\Article;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\User;
use App\Services\CommandeAnnulationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Couvre le cœur du refactor du workflow d'annulation :
 * - le client peut demander l'annulation d'une commande éligible ;
 * - l'administrateur seul peut la valider, ce qui restitue le stock ;
 * - chaque étape est historisée (traçabilité) ;
 * - les règles de cohérence empêchent les transitions invalides.
 */
class CommandeAnnulationTest extends TestCase
{
    use RefreshDatabase;

    private function creerCommandeAvecLigne(User $client, Article $article, int $quantite = 2): Commande
    {
        $commande = Commande::create([
            'numeroCommande' => 'CMD-TEST-'.uniqid(),
            'montantTotal' => $article->prix * $quantite,
            'statut' => 'validee',
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        LigneCommande::create([
            'idCommande' => $commande->idCommande,
            'idArticle' => $article->idArticle,
            'quantite' => $quantite,
            'prixUnitaire' => $article->prix,
        ]);

        return $commande;
    }

    public function test_le_client_peut_demander_lannulation_dune_commande_validee(): void
    {
        $this->seed(RoleSeeder::class);
        // Isoler les envois d'email pour éviter les appels réseau bloquants en test
        Mail::fake();

        $client = User::factory()->create();
        $client->assignRole('client');
        $article = Article::factory()->create(['quantiteStock' => 10]);
        $commande = $this->creerCommandeAvecLigne($client, $article);

        (new CommandeAnnulationService)->demanderAnnulation($commande, $client, 'Je me suis trompé de produit');

        $commande->refresh();
        $this->assertEquals('demande_annulation', $commande->statut);
        $this->assertEquals('validee', $commande->statutAvantAnnulation);
        $this->assertNotNull($commande->motifAnnulation);
        $this->assertDatabaseHas('historique_commandes', [
            'idCommande' => $commande->idCommande,
            'action' => 'demande_annulation',
        ]);

        // S'assurer qu'aucun envoi d'email synchrone n'a eu lieu pendant ce test
        Mail::assertNothingSent();
    }

    public function test_la_validation_par_ladmin_restitue_le_stock_et_historise(): void
    {
        $this->seed(RoleSeeder::class);
        $client = User::factory()->create();
        $client->assignRole('client');
        $admin = User::factory()->create();
        $admin->assignRole('administrateur');

        $article = Article::factory()->create(['quantiteStock' => 10]);
        $commande = $this->creerCommandeAvecLigne($client, $article, quantite: 3);

        $service = new CommandeAnnulationService;
        $service->demanderAnnulation($commande, $client, 'Changement d\'avis');
        $service->validerAnnulation($commande->fresh(), $admin);

        $commande->refresh();
        $article->refresh();

        $this->assertEquals('annulee', $commande->statut);
        // Stock restitué : 10 - 3 (jamais décrémenté ici, la commande de
        // test est créée directement) + 3 (restitution) = 13
        $this->assertEquals(13, $article->quantiteStock);
        $this->assertDatabaseHas('historique_commandes', [
            'idCommande' => $commande->idCommande,
            'action' => 'annulation_validee',
        ]);
        $this->assertDatabaseHas('mouvements_stock', [
            'idArticle' => $article->idArticle,
            'typeMouvement' => 'retour',
            'quantite' => 3,
        ]);
    }

    public function test_le_refus_necessite_un_motif_et_retablit_le_statut_precedent(): void
    {
        $this->seed(RoleSeeder::class);
        $client = User::factory()->create();
        $client->assignRole('client');
        $admin = User::factory()->create();
        $admin->assignRole('administrateur');

        $article = Article::factory()->create();
        $commande = $this->creerCommandeAvecLigne($client, $article);

        $service = new CommandeAnnulationService;
        $service->demanderAnnulation($commande, $client, 'Motif client');
        $service->refuserAnnulation($commande->fresh(), $admin, 'Commande déjà en préparation');

        $commande->refresh();
        $this->assertEquals('validee', $commande->statut);
        $this->assertNull($commande->motifAnnulation);
        $this->assertDatabaseHas('historique_commandes', [
            'idCommande' => $commande->idCommande,
            'action' => 'annulation_refusee',
            'commentaire' => 'Commande déjà en préparation',
        ]);
    }

    public function test_impossible_de_valider_sans_demande_dannulation_en_cours(): void
    {
        $this->seed(RoleSeeder::class);
        $client = User::factory()->create();
        $client->assignRole('client');
        $admin = User::factory()->create();
        $admin->assignRole('administrateur');

        $article = Article::factory()->create();
        // Statut "validee", pas de demande d'annulation en cours.
        $commande = $this->creerCommandeAvecLigne($client, $article);

        $this->expectException(CommandeAnnulationException::class);

        (new CommandeAnnulationService)->validerAnnulation($commande, $admin);
    }

    public function test_impossible_de_demander_lannulation_dune_commande_deja_en_livraison(): void
    {
        $this->seed(RoleSeeder::class);
        $client = User::factory()->create();
        $client->assignRole('client');
        $article = Article::factory()->create();

        $commande = $this->creerCommandeAvecLigne($client, $article);
        $commande->update(['statut' => 'en_livraison']);

        $this->expectException(CommandeAnnulationException::class);

        (new CommandeAnnulationService)->demanderAnnulation($commande, $client, 'Trop tard ?');
    }
}
