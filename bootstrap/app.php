<?php

use App\Http\Middleware\EnsureAdminApiToken;
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
        $middleware->alias([
            'admin.api' => EnsureAdminApiToken::class,
        ]);
        $middleware->preventRequestForgery(except: [
            'sitemap.xml',
            'robots.txt',
        ]);
        $middleware->encryptCookies(except: [
            'sitemap.xml',
            'robots.txt',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
