<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Apply to web group instead of globally
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\PreventBackHistory::class,

            \App\Http\Middleware\SessionExpiredMiddleware::class,

        ]);

        // Route middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,

            'otp.verified' => \App\Http\Middleware\EnsureOtpIsVerified::class,

            'module.password' => \App\Http\Middleware\ModulePassword::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
