<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Confiance aux proxies (ex: Railway / Load Balancers)
        $middleware->trustProxies(at: '*');

        // 2. Exclure la route IPN de la vérification CSRF
        $middleware->validateCsrfTokens(except: [
            'catalogue/paiement/ipn',
        ]);

        // 3. Enregistrer les alias de middlewares
        $middleware->alias([
            'role' => CheckRole::class,
            'permission' => PermissionMiddleware::class,
            // Middleware custom pour invalider immédiatement les comptes bloqués
            'account.active' => EnsureAccountActive::class,
            // Bloque l'accès tant que l'email n'a pas été confirmé
            'email.verified' => EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
