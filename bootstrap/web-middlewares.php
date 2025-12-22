<?php

/**
 * Web middleware configuration
 *
 * These middlewares are appended to the web middleware group.
 */
return [
    \App\Http\Middleware\MapLoginIdentifier::class,
    \App\Http\Middleware\ApplyFrontendPreferences::class,
    \App\Http\Middleware\TeamsPermission::class,
    \App\Http\Middleware\ConvertStatusToNotification::class,
];
