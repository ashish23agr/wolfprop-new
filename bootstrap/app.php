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
        // Register your custom route-middleware aliases here.
        // In Laravel 5.8 these lived in app/Http/Kernel.php ($routeMiddleware).
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'agent' => \App\Http\Middleware\AgentMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();