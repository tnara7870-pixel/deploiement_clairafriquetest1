<?php

namespace Tests\Feature;

use App\Models\Commande;
use App\Models\Paiement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie le filet de sécurité anti-double-paiement : même si la
 * vérification applicative (token déjà en base ?) était contournée ou
 * ratée à cause d'une course entre retour() et ipn(), la contrainte SQL
 * `unique` sur paiements.referenceTransaction empêche physiquement
 * d'enregistrer deux fois le même paiement PayDunya.
 *
 * On teste directement au niveau base de données plutôt qu'en simulant
 * l'appel HTTP complet à PayDunya (qui nécessiterait de mocker le SDK
 * externe) : c'est cette contrainte SQL qui constitue la véritable
 * garantie d'idempotence, la vérification applicative n'étant qu'une
 * optimisation pour éviter l'exception dans le cas normal.
 */
class PaiementIdempotenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deux_paiements_ne_peuvent_pas_partager_la_meme_reference_transaction(): void
    {
        $this->seed(RoleSeeder::class);

        $client = User::factory()->create();
        $client->assignRole('client');

        $commandeA = Commande::create([
            'numeroCommande' => 'CMD-A-0001',
            'montantTotal' => 5000,
            'statut' => 'validee',
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        $commandeB = Commande::create([
            'numeroCommande' => 'CMD-B-0001',
            'montantTotal' => 5000,
            'statut' => 'validee',
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        Paiement::create([
            'modePaiement' => 'wave',
            'montant' => 5000,
            'statutPaiement' => 'valide',
            'referenceTransaction' => 'PAYDUNYA-TOKEN-IDENTIQUE',
            'idCommande' => $commandeA->idCommande,
        ]);

        $this->expectException(QueryException::class);

        // Même référence de transaction, commande différente : doit être
        // rejeté par la contrainte unique, pas seulement par la logique
        // applicative.
        Paiement::create([
            'modePaiement' => 'wave',
            'montant' => 5000,
            'statutPaiement' => 'valide',
            'referenceTransaction' => 'PAYDUNYA-TOKEN-IDENTIQUE',
            'idCommande' => $commandeB->idCommande,
        ]);
    }

    public function test_une_commande_ne_peut_avoir_quun_seul_paiement(): void
    {
        $this->seed(RoleSeeder::class);

        $client = User::factory()->create();
        $client->assignRole('client');

        $commande = Commande::create([
            'numeroCommande' => 'CMD-C-0001',
            'montantTotal' => 3000,
            'statut' => 'validee',
            'idUtilisateur' => $client->idUtilisateur,
        ]);

        Paiement::create([
            'modePaiement' => 'orange_money',
            'montant' => 3000,
            'statutPaiement' => 'valide',
            'referenceTransaction' => 'REF-001',
            'idCommande' => $commande->idCommande,
        ]);

        $this->expectException(QueryException::class);

        Paiement::create([
            'modePaiement' => 'orange_money',
            'montant' => 3000,
            'statutPaiement' => 'valide',
            'referenceTransaction' => 'REF-002',
            'idCommande' => $commande->idCommande,
        ]);
    }
}
