<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CamelCaseToSnakeCaseMiddleware; // Importamos el Middleware
use App\Http\Middleware\CustomCorsMiddleware; // Importamos nuestro middleware de CORS personalizado

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Agregamos nuestro middleware personalizado de CORS al grupo 'api'
        $middleware->api(prepend: [
            CustomCorsMiddleware::class, // Usamos nuestro middleware personalizado
            CamelCaseToSnakeCaseMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
