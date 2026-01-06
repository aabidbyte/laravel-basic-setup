<?php

/**
 * Web middleware configuration
 *
 * These middlewares are appended to the web middleware group.
 */
return [
    \Spatie\Csp\AddCspHeaders::class,
    \App\Http\Middleware\Auth\MapLoginIdentifier::class,
    \App\Http\Middleware\Preferences\ApplyFrontendPreferences::class,
    \App\Http\Middleware\Teams\TeamsPermission::class,
];
