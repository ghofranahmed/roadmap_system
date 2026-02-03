<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    // =========================
    // Register Middlewares
    // =========================
    ->withMiddleware(function (Middleware $middleware): void {

        // Alias for Admin Middleware
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

    })

    // =========================
    // Exception Handling
    // =========================
    ->withExceptions(function (Exceptions $exceptions): void {

        // Handle unauthenticated requests (Sanctum / auth)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'غير مصرح. يرجى تسجيل الدخول.',
                    'error'   => 'Unauthenticated',
                ], 401);
            }

            return redirect()->guest(route('login'));
        });

    })

    ->create();
