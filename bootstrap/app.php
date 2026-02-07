<?php

use App\Services\ErrorHandling\ErrorHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: require __DIR__ . '/web-middlewares.php');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom error handler with toast notifications and database logging
        $exceptions->renderable(function (Throwable $e, Request $request) {
            // Skip if error handling system is disabled
            if (! config('error-handling.enabled', false)) {
                return null; // Let Laravel handle it
            }

            $handler = app(ErrorHandler::class);

            // Skip validation exceptions - let Laravel handle them normally
            // but our handler will still send a toast notification
            if ($e instanceof ValidationException) {
                $handler->report($e, $request);

                return null; // Let Laravel handle the response
            }

            // Handle all other exceptions through our custom handler
            return $handler->handle($e, $request);
        });
    })->create();
