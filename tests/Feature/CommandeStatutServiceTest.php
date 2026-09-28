<?php

namespace Tests\Feature;

use App\Exceptions\CommandeAnnulationException;
use App\Models\Article;
use App\Models\Commande;
use App\Models\Livraison;
use App\Models\Paiement;
use App\Models\User;
use App\Services\CommandeStatutService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre CommandeStatutService : progression uniquement vers l'avant
 * (en_attente → validee → en_livraison → livree), aucune régression,
 * aucune transition vers un statut d'annulation, incompatibilité entre
 * "en_livraison" et un retrait en boutique, et effets de bord (paiement
 * espèces validé automatiquement, dateLivraison renseignée).
 */
class CommandeStatutServiceTest extends TestCase
{
    use RefreshDatabase;

    private function creerCommande(string $statut, string $modeLivraison = 'domicile'): Commande
    {
        $client = User::factory()->create();
        $client->assignRole('client');
        $article = Article::factory()->create();

        $commande = Commande::create([
            'numeroCommande' => 'CMD-TEST-'.uniqid(),
            'montantTotal' => $article->prix,
            'statut' => $statut,
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        Livraison::create([
            'idCommande' => $commande->idCommande,
            'modeLivraison' => $modeLivraison,
            'pointVente' => $modeLivraison === 'boutique' ? 'ucad' : null,
            'pointVenteAttribue' => 'ucad',
            'statutLivraison' => 'preparee',
        ]);

        Paiement::create([
            'idCommande' => $commande->idCommande,
            'modePaiement' => 'especes',
            'montant' => $article->prix,
            'statutPaiement' => 'en_attente',
        ]);

        return $commande;
    }

    public function test_la_progression_normale_avance_dun_palier_a_lautre(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('en_attente');

        (new CommandeStatutService)->changerStatut($commande, 'validee');
        $this->assertEquals('validee', $commande->fresh()->statut);

        (new CommandeStatutService)->changerStatut($commande, 'en_livraison');
        $this->assertEquals('en_livraison', $commande->fresh()->statut);

        (new CommandeStatutService)->changerStatut($commande, 'livree');
        $commande->refresh();
        $this->assertEquals('livree', $commande->statut);
        $this->assertNotNull($commande->livraison->dateLivraison);
        $this->assertEquals('livree', $commande->livraison->statutLivraison);
    }

    public function test_impossible_de_revenir_en_arriere(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('en_livraison');

        $this->expectException(CommandeAnnulationException::class);

        (new CommandeStatutService)->changerStatut($commande, 'validee');
    }

    public function test_impossible_de_faire_regresser_une_commande_livree(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('livree');

        $this->expectException(CommandeAnnulationException::class);

        (new CommandeStatutService)->changerStatut($commande, 'en_attente');
    }

    public function test_le_statut_en_livraison_est_refuse_pour_un_retrait_en_boutique(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('validee', modeLivraison: 'boutique');

        $this->expectException(CommandeAnnulationException::class);

        (new CommandeStatutService)->changerStatut($commande, 'en_livraison');
    }

    public function test_ce_service_naccepte_pas_de_faire_transiter_vers_annulee(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('en_attente');

        $this->expectException(CommandeAnnulationException::class);

        (new CommandeStatutService)->changerStatut($commande, 'annulee');
    }

    public function test_impossible_de_changer_le_statut_pendant_une_demande_dannulation_en_cours(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('demande_annulation');

        $this->expectException(CommandeAnnulationException::class);

        (new CommandeStatutService)->changerStatut($commande, 'validee');
    }

    public function test_rejouer_le_meme_statut_est_un_no_op_silencieux(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('validee');

        (new CommandeStatutService)->changerStatut($commande, 'validee');

        $this->assertEquals('validee', $commande->fresh()->statut);
        // Aucune entrée d'historique ne doit être créée pour un no-op.
        $this->assertDatabaseCount('historique_commandes', 0);
    }

    public function test_faire_progresser_la_commande_valide_automatiquement_un_paiement_especes_en_attente(): void
    {
        $this->seed(RoleSeeder::class);
        $commande = $this->creerCommande('en_attente');

        (new CommandeStatutService)->changerStatut($commande, 'validee');

        $this->assertEquals('valide', $commande->fresh()->paiement->statutPaiement);
    }
}
