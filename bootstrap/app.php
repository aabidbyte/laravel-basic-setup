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
            if (! config('error-handling.enabled', false)) {
                return null;
            }

            $handler = app(ErrorHandler::class);

            if ($e instanceof ValidationException) {
                $handler->report($e, $request);

                return null;
            }

            if ($handler->shouldUseDefaultHttpRendering($e, $request)) {
                return null;
            }

            return $handler->handle($e, $request);
        });
    })->create();
