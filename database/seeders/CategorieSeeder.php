<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nomCategorie' => 'Livres scolaires',     'description' => 'Manuels et livres pour élèves et étudiants'],
            ['nomCategorie' => 'Livres littérature',   'description' => 'Romans, poésie et littérature générale'],
            ['nomCategorie' => 'Papeterie',            'description' => 'Cahiers, stylos, crayons et fournitures de base'],
            ['nomCategorie' => 'Fournitures bureau',   'description' => 'Articles de bureau et d\'organisation'],
            ['nomCategorie' => 'Art et Dessin',        'description' => 'Matériel artistique et de dessin'],
            ['nomCategorie' => 'Carterie',             'description' => 'Cartes, enveloppes et papier cadeau'],
            ['nomCategorie' => 'Informatique',         'description' => 'Accessoires et fournitures informatiques'],
        ];

        foreach ($categories as $cat) {
            Categorie::create([
                'nomCategorie' => $cat['nomCategorie'],
                'description' => $cat['description'],
                'statut' => true,
            ]);
        }
    }
}
