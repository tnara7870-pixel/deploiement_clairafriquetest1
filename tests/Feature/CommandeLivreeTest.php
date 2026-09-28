<?php

namespace Tests\Feature;

use App\Exceptions\CommandeAnnulationException;
use App\Models\Article;
use App\Models\Commande;
use App\Models\User;
use App\Services\CommandeAnnulationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandeLivreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_impossible_de_demander_lannulation_sur_commande_deja_livree(): void
    {
        $this->seed(RoleSeeder::class);

        $client = User::factory()->create();
        $client->assignRole('client');

        $article = Article::factory()->create();

        $commande = Commande::create([
            'numeroCommande' => 'CMD-TEST-'.uniqid(),
            'montantTotal' => $article->prix,
            'statut' => 'livree',
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        $this->expectException(CommandeAnnulationException::class);

        // Tenter de demander annulation sur commande déjà livrée => exception attendue
        (new CommandeAnnulationService)->demanderAnnulation($commande, $client, 'Trop tard');
    }
}
