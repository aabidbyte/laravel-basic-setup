<?php

/**
 * Web middleware configuration
 *
 * These middlewares are appended to the web middleware group.
 */
return [
    \App\Http\Middleware\Auth\MapLoginIdentifier::class,
    \App\Http\Middleware\Preferences\ApplyFrontendPreferences::class,
    \App\Http\Middleware\Teams\TeamsPermission::class,
];
