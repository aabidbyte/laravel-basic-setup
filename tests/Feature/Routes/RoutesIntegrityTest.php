<?php

use Illuminate\Support\Facades\Route;

/**
 * Routes Integrity Tests
 *
 * These tests ensure there are no route collisions, duplicate names, or issues
 * with the route configuration that could cause unexpected behavior.
 *
 * Note: Framework package routes (Fortify, Horizon, Log-Viewer, etc.) are excluded
 * as they follow their own conventions and have their own auth mechanisms.
 */

/**
 * Routes to skip in tests (framework/package routes).
 */
function shouldSkipRoute(string $uri, ?string $name = null): bool
{
    // Skip by URI prefix
    $skipPrefixes = [
        '_debugbar',
        'telescope',
        'horizon',
        'livewire',
        'sanctum',
        'api/',
        'broadcasting',
        'up',  // Laravel health check
        'admin/system/queue-monitor',  // Horizon
        'admin/system/log-viewer',  // Log Viewer
    ];

    foreach ($skipPrefixes as $prefix) {
        if (\str_starts_with($uri, $prefix)) {
            return true;
        }
    }

    // Skip by route name prefix
    if ($name) {
        $skipNamePrefixes = [
            'debugbar.',
            'telescope.',
            'horizon.',
            'livewire.',
            'sanctum.',
            'ignition.',
            'log-viewer.',
            'two-factor.',        // Fortify 2FA routes
            'user-profile-information.',  // Fortify routes
            'user-password.',     // Fortify routes
            'password.',          // Fortify routes
            'verification.',      // Fortify routes
        ];

        foreach ($skipNamePrefixes as $prefix) {
            if (\str_starts_with($name, $prefix)) {
                return true;
            }
        }
    }

    return false;
}

it('has no duplicate route names', function () {
    $routes = Route::getRoutes()->getRoutesByName();

    $names = [];
    $duplicates = [];

    foreach ($routes as $name => $route) {
        if (isset($names[$name])) {
            $duplicates[] = $name;
        }
        $names[$name] = true;
    }

    expect($duplicates)->toBeEmpty(
        'Duplicate route names found: ' . \implode(', ', $duplicates),
    );
});

it('has no route URI collisions for same HTTP method', function () {
    $routes = Route::getRoutes()->getRoutes();

    $urisByMethod = [];
    $collisions = [];

    foreach ($routes as $route) {
        $uri = $route->uri();
        $methods = $route->methods();

        foreach ($methods as $method) {
            // Normalize the URI by replacing parameter names with a placeholder
            // e.g., /users/{user} and /users/{id} would both become /users/{param}
            $normalizedUri = \preg_replace('/\{[^}]+\}/', '{param}', $uri);
            $key = $method . ':' . $normalizedUri;

            if (isset($urisByMethod[$key])) {
                // Check if it's actually the same route (same action)
                $existingAction = $urisByMethod[$key]['action'];
                $currentAction = $route->getActionName();

                if ($existingAction !== $currentAction) {
                    $collisions[] = \sprintf(
                        '%s %s (defined in %s, conflicts with %s)',
                        $method,
                        $uri,
                        $currentAction,
                        $existingAction,
                    );
                }
            } else {
                $urisByMethod[$key] = [
                    'uri' => $uri,
                    'action' => $route->getActionName(),
                ];
            }
        }
    }

    expect($collisions)->toBeEmpty(
        'Route URI collisions found: ' . PHP_EOL . \implode(PHP_EOL, $collisions),
    );
});

it('all routes have names', function () {
    $routes = Route::getRoutes()->getRoutes();

    $unnamedRoutes = [];

    foreach ($routes as $route) {
        $name = $route->getName();
        $uri = $route->uri();

        // Skip framework/package routes
        if (shouldSkipRoute($uri, $name)) {
            continue;
        }

        if (empty($name)) {
            $methods = \implode('|', $route->methods());
            $unnamedRoutes[] = "{$methods} {$uri}";
        }
    }

    expect($unnamedRoutes)->toBeEmpty(
        'Unnamed routes found (all routes should have names): ' . PHP_EOL . \implode(PHP_EOL, $unnamedRoutes),
    );
});

it('route names follow naming conventions', function () {
    $routes = Route::getRoutes()->getRoutesByName();

    $invalidNames = [];

    foreach ($routes as $name => $route) {
        $uri = $route->uri();

        // Skip framework/package routes
        if (shouldSkipRoute($uri, $name)) {
            continue;
        }

        // Route names should be lowercase with dots as separators (or camelCase for legacy reasons)
        if (! \preg_match('/^[a-zA-Z0-9]+(\.([a-zA-Z0-9]+(-[a-zA-Z0-9]+)*))*$/', $name)) {
            $invalidNames[] = $name;
        }
    }

    expect($invalidNames)->toBeEmpty(
        'Route names not following naming convention (lowercase/camelCase with dots): ' . PHP_EOL . \implode(PHP_EOL, $invalidNames),
    );
});

it('protected routes have appropriate middleware', function () {
    $routes = Route::getRoutes()->getRoutes();

    $unprotectedRoutes = [];

    // Route prefixes that should require auth middleware
    $protectedPrefixes = ['users', 'settings', 'notifications'];

    foreach ($routes as $route) {
        $uri = $route->uri();
        $name = $route->getName();
        $middleware = $route->middleware();

        // Skip framework/package routes (they have their own auth)
        if (shouldSkipRoute($uri, $name)) {
            continue;
        }

        foreach ($protectedPrefixes as $prefix) {
            if (\str_starts_with($uri, $prefix) || \str_starts_with($uri, $prefix . '/')) {
                if (! \in_array('auth', $middleware, true)) {
                    $routeName = $route->getName() ?? $uri;
                    $unprotectedRoutes[] = "{$routeName} ({$uri}) - missing 'auth' middleware";
                }
                break;
            }
        }
    }

    expect($unprotectedRoutes)->toBeEmpty(
        'Protected routes missing auth middleware: ' . PHP_EOL . \implode(PHP_EOL, $unprotectedRoutes),
    );
});
