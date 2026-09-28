<?php

namespace App\Providers;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Paiement;
use App\Policies\CommandePolicy;
use App\Policies\PaiementPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Commande::class, CommandePolicy::class);
        Gate::policy(Paiement::class, PaiementPolicy::class);

        // Liste des catégories actives pour le menu déroulant du header
        // client (survol de "Catégories") — partagée avec le layout
        // plutôt qu'avec chaque contrôleur, puisque le header apparaît
        // sur toutes les pages de l'espace client.
        View::composer('layouts.client', function ($view) {
            $view->with(
                'categoriesMenu',
                Categorie::where('statut', true)->orderBy('nomCategorie')->get()
            );
        });
    }
}