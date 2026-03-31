<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Configuration des Proxies pour Railway
        $middleware->trustProxies(at: '*');

        // 2. ACTIVATION DU FLUX DE DONNÉES INERTIA (INDISPENSABLE)
        // C'est cette ligne qui permet d'envoyer le nom et le rôle à ton interface Vue
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // 3. Tes alias existants
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'clinic.access' => \App\Http\Middleware\EnsureUserBelongsToClinic::class, 
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();