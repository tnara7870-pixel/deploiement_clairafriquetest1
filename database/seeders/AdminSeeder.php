<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            throw new \RuntimeException(
                'ADMIN_PASSWORD doit être défini dans .env avant de lancer AdminSeeder.'
            );
        }

        $admin = User::create([
            'nom' => 'Admin',
            'prenom' => 'ClaireAfrique',
            'email' => env('ADMIN_EMAIL', 'admin@claireafrique.sn'),
            'motDePasse' => Hash::make($password),
            'telephone' => '+221 77 000 00 00',
            'statut' => true,
        ]);

        $admin->assignRole('administrateur');
    }
}
