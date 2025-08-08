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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            // 'performance' => \App\Http\Middleware\PerformanceMiddleware::class,
        ]);
        
        // Middleware de performance removido temporariamente para resolver erro de autoload
        // $middleware->web(append: [
        //     \App\Http\Middleware\PerformanceMiddleware::class,
        // ]);
    })
    ->withProviders([
        \App\Modules\ModuleServiceProvider::class,
        \App\Providers\PermissionServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
