<?php

/**
 * Get the current application environment.
 * Uses config('app.env') to respect config caching, matching Laravel's app()->environment() behavior.
 */
function appEnv(): string
{
    return config('app.env', 'production');
}

/**
 * Return true when the application is running in a production environment.
 */
function isProduction(): bool
{
    $env = strtolower(appEnv());

    return in_array($env, ['production', 'prod'], true);
}

/**
 * Return true when the application is running in a development environment (local or development).
 */
function isDevelopment(): bool
{
    $env = strtolower(appEnv());

    return in_array($env, ['local', 'development', 'dev'], true);
}

/**
 * Return true when the application is running in a staging environment.
 */
function isStaging(): bool
{
    $env = strtolower(appEnv());

    return in_array($env, ['staging', 'stage'], true);
}

/**
 * Return true when the application is running in a local environment.
 */
function isLocal(): bool
{
    return strtolower(appEnv()) === 'local' || isDevelopment();
}

/**
 * Return true when the application is running in a testing environment.
 */
function isTesting(): bool
{
    return strtolower(appEnv()) === 'testing';
}

/**
 * Check if the application environment matches any of the given environments.
 */
function inEnvironment(string ...$environments): bool
{
    $env = strtolower(appEnv());

    foreach ($environments as $environment) {
        if ($env === strtolower($environment)) {
            return true;
        }
    }

    return false;
}

function cspNonce(): string
{
    return Vite::cspNonce();
}
