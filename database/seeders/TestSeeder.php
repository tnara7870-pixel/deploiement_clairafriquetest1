<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Livraison;
use App\Models\Paiement;
use App\Models\StockPointVente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisateurs de test
        $client = User::firstOrCreate(
            ['email' => 'client@test.sn'],
            [
                'nom' => 'Test',
                'prenom' => 'Client',
                'motDePasse' => Hash::make('Password@25'),
                'telephone' => '+221 77 999 99 99',
                'statut' => true,
            ]
        );
        if (! $client->hasRole('client')) {
            $client->assignRole('client');
        }

        $resStock = User::firstOrCreate(
            ['email' => 'stock@test.sn'],
            [
                'nom' => 'Test',
                'prenom' => 'ResStock',
                'motDePasse' => Hash::make('Password@25'),
                'telephone' => '+221 77 888 88 88',
                'statut' => true,
            ]
        );
        if (! $resStock->hasRole('res.stock')) {
            $resStock->assignRole('res.stock');
        }

        $resCommande = User::firstOrCreate(
            ['email' => 'commande@test.sn'],
            [
                'nom' => 'Test',
                'prenom' => 'ResCommande',
                'motDePasse' => Hash::make('Password@25'),
                'telephone' => '+221 77 777 77 77',
                'statut' => true,
            ]
        );
        if (! $resCommande->hasRole('res.commande')) {
            $resCommande->assignRole('res.commande');
        }

        // Articles — uniquement si la table est vide
        if (Article::count() === 0) {
            $articles = [
                ['reference' => 'LIV-001', 'designation' => 'Manuel Mathématiques Terminale',          'prix' => 4500,  'quantiteStock' => 25,  'idCategorie' => 1],
                ['reference' => 'LIV-002', 'designation' => 'L\'Aventure ambiguë — C.H. Kane',         'prix' => 3500,  'quantiteStock' => 15,  'idCategorie' => 2],
                ['reference' => 'PAP-001', 'designation' => 'Cahier grand format 200 pages',            'prix' => 1200,  'quantiteStock' => 8,   'idCategorie' => 3],
                ['reference' => 'PAP-002', 'designation' => 'Ramette papier A4 80g',                   'prix' => 3200,  'quantiteStock' => 50,  'idCategorie' => 3],
                ['reference' => 'PAP-003', 'designation' => 'Stylo bille Bic x10',                     'prix' => 800,   'quantiteStock' => 100, 'idCategorie' => 3],
                ['reference' => 'BUR-001', 'designation' => 'Classeur A4 4 anneaux',                   'prix' => 2500,  'quantiteStock' => 5,   'idCategorie' => 4],
                ['reference' => 'BUR-002', 'designation' => 'Agenda 2026 semainier',                   'prix' => 5000,  'quantiteStock' => 20,  'idCategorie' => 4],
                ['reference' => 'ART-001', 'designation' => 'Lot crayons de couleur 24 pièces',        'prix' => 2800,  'quantiteStock' => 30,  'idCategorie' => 5],
            ];

            foreach ($articles as $art) {
                $quantite = $art['quantiteStock'];
                unset($art['quantiteStock']);

                $article = Article::create(array_merge($art, ['statut' => true, 'quantiteStock' => 0]));

                // Article::booted() vient de créer les 2 lignes de stock par
                // point (à 0) automatiquement. On y répartit maintenant la
                // quantité de démo, sur l'entrepôt principal — cohérent avec
                // la politique appliquée par la migration de bascule sur les
                // données déjà en base (voir audit du 31/08/2026 : avant ce
                // correctif, le stock de démo restait à 0 sur les deux points
                // malgré un quantiteStock non nul, bloquant toute commande).
                StockPointVente::where('idArticle', $article->idArticle)
                    ->where('pointVente', config('pointvente.entrepot_principal'))
                    ->update(['quantiteStock' => $quantite]);

                $article->resynchroniserQuantiteTotale();
            }
        }

        // Commandes de test — uniquement si aucune commande n'existe
        if (Commande::count() === 0) {
            $statutsTest = [
                ['statut' => 'en_attente',   'paiement' => 'en_attente', 'livraison' => 'preparee'],
                ['statut' => 'validee',      'paiement' => 'valide',     'livraison' => 'preparee'],
                ['statut' => 'en_livraison', 'paiement' => 'valide',     'livraison' => 'en_livraison'],
                ['statut' => 'livree',       'paiement' => 'valide',     'livraison' => 'livree'],
                ['statut' => 'annulee',      'paiement' => 'echoue',     'livraison' => 'annulee'],
            ];

            foreach ($statutsTest as $i => $config) {
                $commande = Commande::create([
                    'numeroCommande' => 'CMD-2026-000'.($i + 1),
                    'montantTotal' => rand(3000, 25000),
                    'statut' => $config['statut'],
                    'idUtilisateur' => $client->idUtilisateur,
                ]);

                $articlesTest = Article::inRandomOrder()->limit(2)->get();
                foreach ($articlesTest as $art) {
                    LigneCommande::create([
                        'idCommande' => $commande->idCommande,
                        'idArticle' => $art->idArticle,
                        'quantite' => rand(1, 3),
                        'prixUnitaire' => $art->prix,
                    ]);
                }

                // Point de vente rattaché à la commande — sans ça, ces commandes
                // de démo restent invisibles pour tout compte res.stock/
                // res.commande scopé à un point (voir audit du 31/08/2026) :
                // alterné entre les deux points pour les retraits boutique, afin
                // de pouvoir réellement tester la séparation par point avec ces
                // données de démo.
                $estBoutique = $i % 2 !== 0;
                $pointVente = $estBoutique ? ($i % 4 === 1 ? 'ucad' : 'centre_ville') : null;
                $pointVenteAttribue = $estBoutique ? $pointVente : config('pointvente.entrepot_principal');

                Livraison::create([
                    'idCommande' => $commande->idCommande,
                    'modeLivraison' => $i % 2 === 0 ? 'domicile' : 'boutique',
                    'pointVente' => $pointVente,
                    'pointVenteAttribue' => $pointVenteAttribue,
                    'adresseLivraison' => $i % 2 === 0 ? 'Dakar, Plateau' : null,
                    'statutLivraison' => $config['livraison'],
                ]);

                Paiement::create([
                    'idCommande' => $commande->idCommande,
                    'modePaiement' => $i % 2 === 0 ? 'wave' : 'orange_money',
                    'montant' => $commande->montantTotal,
                    'statutPaiement' => $config['paiement'],
                    'referenceTransaction' => 'REF-TEST-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
