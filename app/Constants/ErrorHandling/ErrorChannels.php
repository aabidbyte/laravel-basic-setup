<?php

declare(strict_types=1);

namespace App\Constants\ErrorHandling;

/**
 * Error notification channel constants.
 *
 * Defines the available channels for error notifications.
 * Used by the error handling configuration and service.
 */
class ErrorChannels
{
    /**
     * Toast notification channel (always enabled).
     */
    public const TOAST = 'toast';

    /**
     * Slack webhook notification channel.
     */
    public const SLACK = 'slack';

    /**
     * Email notification channel.
     */
    public const EMAIL = 'email';

    /**
     * Log file/channel notification.
     */
    public const LOG = 'log';

    /**
     * Database storage channel (for ticketing system).
     */
    public const DATABASE = 'database';

    /**
     * Get all available channel constants.
     *
     * @return array<string> List of all channel identifiers
     */
    public static function all(): array
    {
        return [
            self::TOAST,
            self::SLACK,
            self::EMAIL,
            self::LOG,
            self::DATABASE,
        ];
    }

    /**
     * Get channels that should be rate-limited.
     *
     * @return array<string> Channels that benefit from rate limiting
     */
    public static function rateLimitedChannels(): array
    {
        return [
            self::SLACK,
            self::EMAIL,
        ];
    }
}
