<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Couvre l'inscription (validation, politique de mot de passe) et la
 * connexion (identifiants invalides, verrouillage du compte après 3
 * échecs, réinitialisation du compteur une fois le blocage expiré,
 * compte désactivé) — voir AuthController et App\Rules\MotDePasseComplexe.
 */
class AuthentificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_linscription_cree_un_compte_client_avec_un_panier(): void
    {
        $this->seed(RoleSeeder::class);
        Mail::fake();

        $response = $this->post('/register', [
            'nom' => 'Diallo',
            'prenom' => 'Fatou',
            'email' => 'fatou@example.com',
            'telephone' => '770000000',
            'motDePasse' => 'Motdepasse1!',
            'motDePasse_confirmation' => 'Motdepasse1!',
            // Présent au cas où la case de consentement CGU/confidentialité
            // (validation 'accepted') a été fusionnée dans ce dépôt ; un
            // champ en trop est sans effet si elle ne l'a pas été.
            'conditionsAcceptees' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('utilisateurs', ['email' => 'fatou@example.com']);

        $user = User::where('email', 'fatou@example.com')->first();
        $this->assertTrue($user->hasRole('client'));
        $this->assertDatabaseHas('paniers', ['idUtilisateur' => $user->idUtilisateur]);
    }

    public function test_linscription_refuse_un_mot_de_passe_qui_ne_respecte_pas_la_politique(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->from('/register')->post('/register', [
            'nom' => 'Diallo',
            'prenom' => 'Fatou',
            'email' => 'fatou2@example.com',
            'motDePasse' => 'motdepasse',
            'motDePasse_confirmation' => 'motdepasse',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('motDePasse');
        $this->assertDatabaseMissing('utilisateurs', ['email' => 'fatou2@example.com']);
    }

    public function test_linscription_refuse_un_email_deja_utilise(): void
    {
        $this->seed(RoleSeeder::class);
        User::factory()->create(['email' => 'existe@example.com']);

        $response = $this->from('/register')->post('/register', [
            'nom' => 'Diallo',
            'prenom' => 'Fatou',
            'email' => 'existe@example.com',
            'motDePasse' => 'Motdepasse1!',
            'motDePasse_confirmation' => 'Motdepasse1!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_connexion_reussie_avec_les_bons_identifiants(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['motDePasse' => Hash::make('Motdepasse1!')]);
        $user->assignRole('client');

        $response = $this->post('/login', [
            'email' => $user->email,
            'motDePasse' => 'Motdepasse1!',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_connexion_avec_mauvais_mot_de_passe_incremente_le_compteur_dechecs(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['motDePasse' => Hash::make('Motdepasse1!')]);
        $user->assignRole('client');

        $this->post('/login', ['email' => $user->email, 'motDePasse' => 'FauxMotDePasse1!']);

        $this->assertEquals(1, $user->fresh()->tentativesEchouees);
        $this->assertGuest();
    }

    public function test_le_compte_est_bloque_apres_trois_echecs_et_le_bon_mot_de_passe_est_alors_refuse(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['motDePasse' => Hash::make('Motdepasse1!')]);
        $user->assignRole('client');

        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', ['email' => $user->email, 'motDePasse' => 'FauxMotDePasse1!']);
        }

        $user->refresh();
        $this->assertEquals(3, $user->tentativesEchouees);
        $this->assertNotNull($user->bloqueJusqua);
        $this->assertTrue($user->bloqueJusqua->isFuture());

        // Même le bon mot de passe est refusé tant que le blocage court :
        // le compte est réellement inaccessible, pas seulement le compteur figé.
        $response = $this->post('/login', ['email' => $user->email, 'motDePasse' => 'Motdepasse1!']);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_le_compteur_est_reinitialise_une_fois_le_blocage_expire(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['motDePasse' => Hash::make('Motdepasse1!')]);
        $user->assignRole('client');
        $user->forceFill([
            'tentativesEchouees' => 3,
            'bloqueJusqua' => now()->subMinute(), // blocage déjà expiré
        ])->save();

        $response = $this->post('/login', [
            'email' => $user->email,
            'motDePasse' => 'Motdepasse1!',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertEquals(0, $user->fresh()->tentativesEchouees);
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'motDePasse' => Hash::make('Motdepasse1!'),
            'statut' => false,
        ]);
        $user->assignRole('client');

        $response = $this->post('/login', [
            'email' => $user->email,
            'motDePasse' => 'Motdepasse1!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_un_email_inconnu_et_un_mauvais_mot_de_passe_renvoient_le_meme_message(): void
    {
        // Message générique volontaire (pas d'énumération de comptes) :
        // voir commentaire d'AuthController::login().
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['motDePasse' => Hash::make('Motdepasse1!')]);
        $user->assignRole('client');

        $this->post('/login', ['email' => 'inconnu@example.com', 'motDePasse' => 'Peuimporte1!']);
        $messageEmailInconnu = session('errors')->first('email');

        $this->post('/login', ['email' => $user->email, 'motDePasse' => 'Peuimporte1!']);
        $messageMauvaisMdp = session('errors')->first('email');

        $this->assertStringContainsString('Identifiants incorrects', $messageEmailInconnu);
        $this->assertStringContainsString('Identifiants incorrects', $messageMauvaisMdp);
    }
}
