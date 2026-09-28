<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 *
 * Corrigée pour correspondre au schéma réel de la table "utilisateurs"
 * (nom/prenom/motDePasse/telephone/statut) : la factory par défaut de
 * Laravel (name/password/email_verified_at) ne matchait aucune colonne
 * réelle et aurait fait planter tout test qui l'utilisait.
 */
class UserFactory extends Factory
{
    protected static ?string $motDePasse;

    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'motDePasse' => static::$motDePasse ??= Hash::make('password'),
            'telephone' => fake()->numerify('77#######'),
            'statut' => true,
        ];
    }
}
