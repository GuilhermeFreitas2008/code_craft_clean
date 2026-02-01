<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful; // ADICIONA ISTO

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <-- adiciona esta linha
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Adiciona o Sanctum ao grupo API
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    
    ->withExceptions(function (Exceptions $exceptions) {
       
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
        // Verifica se o pedido é para a API (JSON)
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Não tens permissão para esta ação.',
            ], 403);
        }
        });
        
    })->create();
