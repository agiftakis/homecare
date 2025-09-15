<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'agency_admin' => \App\Http\Middleware\CheckAgencyAdmin::class,
            // ✅ TIMEZONE FIX: Register the new middleware for setting the timezone.
            'timezone' => \App\Http\Middleware\SetTimezone::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

