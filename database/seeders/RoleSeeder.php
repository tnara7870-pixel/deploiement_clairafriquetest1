<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Réinitialiser le cache des permissions
        app()[PermissionRegistrar::class]
            ->forgetCachedPermissions();

        // Créer les rôles
        Role::create(['name' => 'client',        'guard_name' => 'web']);
        Role::create(['name' => 'res.stock',     'guard_name' => 'web']);
        Role::create(['name' => 'res.commande',  'guard_name' => 'web']);
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
    }
}
