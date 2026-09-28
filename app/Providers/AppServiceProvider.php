<?php

namespace App\Providers;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Paiement;
use App\Policies\CommandePolicy;
use App\Policies\PaiementPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

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
        // Extension du driver Mail pour Brevo
        Mail::extend('brevo', function (array $config = []) {
            return (new BrevoTransportFactory)->create(
                new Dsn('brevo+api', 'default', $config['key'] ?? null)
            );
        });

        // Force HTTPS en environnement de production (ex: Railway)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Enregistrement des Policies
        Gate::policy(Commande::class, CommandePolicy::class);
        Gate::policy(Paiement::class, PaiementPolicy::class);

        // Partage des catégories actives avec la vue layout client
        View::composer('layouts.client', function ($view) {
            $view->with(
                'categoriesMenu',
                Categorie::where('statut', true)->orderBy('nomCategorie')->get()
            );
        });
    }
}
