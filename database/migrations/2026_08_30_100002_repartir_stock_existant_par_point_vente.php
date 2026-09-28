<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * IMPORTANT — à vérifier avant de déployer en production :
 *
 * Le système ne peut pas deviner la VRAIE répartition physique du
 * stock existant entre UCAD et Centre-ville (cette information
 * n'existe nulle part). Par prudence, cette migration ne l'invente
 * pas : elle verse l'intégralité du stock actuel de chaque article
 * dans l'entrepôt principal (config('pointvente.entrepot_principal'),
 * actuellement 'centre_ville'), et met l'autre point à 0.
 *
 * ⚠ Une fois cette migration jouée, un responsable de stock doit
 * corriger manuellement les quantités réelles de chaque point (via un
 * mouvement d'ajustement) pour refléter la réalité du terrain. Tant
 * que ce n'est pas fait, le point non-principal apparaîtra vide.
 *
 * Même logique pour les commandes déjà en base : les retraits en
 * boutique reprennent le point choisi par le client (pointVente) ;
 * les livraisons à domicile se voient attribuer l'entrepôt principal,
 * pour rester cohérentes avec le nouveau système de filtrage — mais
 * comme leur stock a déjà été décrémenté avant l'existence de cette
 * fonctionnalité, cela n'a aucun impact rétroactif sur les quantités.
 */
return new class extends Migration
{
    public function up(): void
    {
        $entrepotPrincipal = config('pointvente.entrepot_principal', 'ucad');
        $autrePoint = $entrepotPrincipal === 'ucad' ? 'centre_ville' : 'ucad';

        $articles = DB::table('articles')->select('idArticle', 'quantiteStock')->get();

        foreach ($articles as $article) {
            DB::table('stocks_points_vente')->insert([
                'idArticle' => $article->idArticle,
                'pointVente' => $entrepotPrincipal,
                'quantiteStock' => $article->quantiteStock,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('stocks_points_vente')->insert([
                'idArticle' => $article->idArticle,
                'pointVente' => $autrePoint,
                'quantiteStock' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('livraisons')
            ->whereNotNull('pointVente')
            ->update(['pointVenteAttribue' => DB::raw('pointVente')]);

        DB::table('livraisons')
            ->whereNull('pointVente')
            ->update(['pointVenteAttribue' => $entrepotPrincipal]);
    }

    public function down(): void
    {
        DB::table('stocks_points_vente')->truncate();

        DB::table('livraisons')->update(['pointVenteAttribue' => null]);
    }
};
