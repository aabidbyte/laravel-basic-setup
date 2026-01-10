<?php

/**
 * Error Handling Configuration
 *
 * This configuration file controls the centralized error handling system.
 * It supports multiple notification channels, environment-specific behavior,
 * and database storage for future ticketing system integration.
 */

use App\Constants\ErrorHandling\ErrorChannels;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Error Handling System
    |--------------------------------------------------------------------------
    |
    | When disabled, the error handling system will not intercept exceptions
    | and Laravel's default exception handling will be used instead.
    | Enabled by default in production environment.
    |
    */
    'enabled' => env('ERROR_HANDLING_ENABLED', isProduction()),

    /*
    |--------------------------------------------------------------------------
    | Reference ID Configuration
    |--------------------------------------------------------------------------
    |
    | The reference ID format is: PREFIX-YYYYMMDD-XXXXXX
    | Example: ERR-20260108-ABC123
    |
    */
    'reference_prefix' => env('ERROR_REFERENCE_PREFIX', 'ERR'),

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Configure which channels should receive error notifications.
    | Each channel can be enabled/disabled per environment via .env
    |
    */
    'channels' => [
        ErrorChannels::TOAST => [
            'enabled' => true, // Always enabled - primary user feedback
        ],

        ErrorChannels::SLACK => [
            'enabled' => env('ERROR_HANDLING_SLACK_ENABLED', false),
            'webhook_url' => env('ERROR_HANDLING_SLACK_WEBHOOK_URL'),
            'username' => env('ERROR_HANDLING_SLACK_USERNAME', 'Error Handler'),
            'emoji' => env('ERROR_HANDLING_SLACK_EMOJI', ':rotating_light:'),
        ],

        ErrorChannels::EMAIL => [
            'enabled' => env('ERROR_HANDLING_EMAIL_ENABLED', false),
            'recipients' => env('ERROR_HANDLING_EMAIL_RECIPIENTS'), // Comma-separated list
            'queue' => env('ERROR_HANDLING_EMAIL_QUEUE', true), // Queue for async delivery
        ],

        ErrorChannels::LOG => [
            'enabled' => env('ERROR_HANDLING_LOG_ENABLED', true),
            'channel' => env('ERROR_HANDLING_LOG_CHANNEL', 'error'),
        ],

        ErrorChannels::DATABASE => [
            'enabled' => env('ERROR_HANDLING_DATABASE_ENABLED', true),
            'retention_days' => env('ERROR_HANDLING_RETENTION_DAYS', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exceptions to Exclude from Reporting
    |--------------------------------------------------------------------------
    |
    | These exception types will still show toast notifications to users
    | but won't be logged to database, Slack, or email channels.
    |
    */
    'dont_report' => [
        ValidationException::class,
        AuthenticationException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Prevent notification spam during error storms by limiting
    | the number of notifications per minute per IP address.
    |
    */
    'rate_limit' => [
        'enabled' => env('ERROR_HANDLING_RATE_LIMIT', true),
        'max_per_minute' => env('ERROR_HANDLING_MAX_PER_MINUTE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Fields
    |--------------------------------------------------------------------------
    |
    | Request fields that should be sanitized before storage.
    | These values will be replaced with '[REDACTED]' in error logs.
    |
    */
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'secret',
        'api_key',
        'api_secret',
        'credit_card',
        'card_number',
        'cvv',
        'cvc',
        'ssn',
        'authorization',
    ],
];
