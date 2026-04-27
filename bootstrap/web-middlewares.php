<?php

use App\Http\Middleware\Auth\ResolveRequestIdentifier;
use App\Http\Middleware\Preferences\ApplyFrontendPreferences;
use App\Http\Middleware\Security\EnsureIdempotentRequest;
use App\Http\Middleware\Teams\TeamsPermission;
use Spatie\Csp\AddCspHeaders;

/**
 * Web middleware configuration
 *
 * These middlewares are appended to the web middleware group.
 */
return [
    EnsureIdempotentRequest::class,
    AddCspHeaders::class,
    ResolveRequestIdentifier::class,
    ApplyFrontendPreferences::class,
    TeamsPermission::class,
];
