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
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware bawaan lo (biarin aja)
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // 👇 WAJIB DI VERCEL: Biar Laravel sadar dia di balik proxy HTTPS
        $middleware->trustProxies(at: '*');

        // 👇 WAJIB BUAT FONNTE: Bypass sistem keamanan CSRF buat URL ini
        $middleware->validateCsrfTokens(except: [
            'fonnte-webhook', // Nama rutenya harus sama persis kayak di web.php
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();