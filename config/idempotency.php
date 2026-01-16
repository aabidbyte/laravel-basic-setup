<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Idempotency System
    |--------------------------------------------------------------------------
    |
    | This configuration controls the request idempotency system, which prevents
    | duplicate request processing by using content-based fingerprinting and
    | Redis locks.
    |
    */

    /**
     * Enable or disable the idempotency system.
     */
    'enabled' => env('IDEMPOTENCY_ENABLED', true),

    /**
     * Fallback TTL (in seconds) for locks.
     *
     * This is a safety mechanism to prevent orphaned locks if the process
     * crashes before the terminate() method runs. The lock is normally
     * released in terminate() after the response is sent.
     */
    'fallback_ttl' => env('IDEMPOTENCY_FALLBACK_TTL', 60),

    /**
     * Cache store to use for locks.
     *
     * Must be a persistent store (redis, memcached, etc.).
     * Do not use 'array' or 'file' in production.
     */
    'cache_store' => env('IDEMPOTENCY_CACHE_STORE', 'redis'),

    /**
     * Paths to exclude from idempotency checks.
     *
     * These paths will bypass the idempotency middleware entirely.
     */
    'excluded_paths' => [
        // Laravel Horizon
        'horizon/*',

        // Laravel Telescope
        'telescope/*',

        // Laravel Debugbar
        '_debugbar/*',

        // Log Viewer
        'log-viewer/*',
        'admin/system/log-viewer/*',

        // Reverb/Broadcasting
        'broadcasting/*',
        'reverb/*',

        // Livewire (internal requests)
        'livewire/*',

        // Sanctum (CSRF cookie)
        'sanctum/*',
    ],

    /**
     * HTTP methods to exclude from idempotency checks.
     *
     * GET, HEAD, and OPTIONS are naturally idempotent and should be excluded.
     */
    'excluded_methods' => ['GET', 'HEAD', 'OPTIONS'],
];
