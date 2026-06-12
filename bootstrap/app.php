<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\RedirectIfProfileIncomplete::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\EnsureTwoFactorEnabled::class,
            'throttle:global',
        ]);
        //
        $middleware->alias([
            'admin.role'       => \App\Http\Middleware\EnsureAdminRole::class,
            'prevent.duplicate'=> \App\Http\Middleware\PreventDuplicateSubmission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            // Códigos de estado que queremos renderizar con nuestra vista de Error.vue
            $customStatusCodes = [403, 404, 429, 500, 503];

            // Siempre mostraremos el error 429 personalizado. Los demás (500, etc.)
            // solo se mostrarán en producción, a menos que quieras forzarlos.
            $showCustomError = in_array($response->getStatusCode(), $customStatusCodes);

            // Si es 500 y estamos en local, preferimos ver el detalle técnico de Laravel.
            if ($response->getStatusCode() === 500 && app()->environment(['local', 'testing'])) {
                $showCustomError = false;
            }

            if ($showCustomError) {
                return \Inertia\Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
