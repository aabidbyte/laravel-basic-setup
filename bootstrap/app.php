<?php

use App\Http\Middleware\Tenancy\UniversalMiddleware;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: ['*']);

        $middleware->alias([
            'universal' => UniversalMiddleware::class,
        ]);

        $middleware->web(append: require __DIR__ . '/web-middlewares.php');

        $middleware->web(append: [
            UniversalMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            $handler = app(ErrorHandler::class);

            // Handle validation exceptions specifically to maintain original behavior
            if ($e instanceof ValidationException) {
                // If the error handler returns null, we let Laravel handle it
                $handler->handle($e, $request);

                return null; // Let Laravel handle the response
            }

            // Handle all other exceptions through our custom handler
            return $handler->handle($e, $request);
        });
    })->create();
