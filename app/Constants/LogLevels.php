<?php

namespace App\Constants;

/**
 * Log level constants for Laravel logging.
 *
 * CRITICAL RULE: All log level names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for log levels throughout the application.
 */
class LogLevels
{
    public const EMERGENCY = 'emergency';

    public const ALERT = 'alert';

    public const CRITICAL = 'critical';

    public const ERROR = 'error';

    public const WARNING = 'warning';

    public const NOTICE = 'notice';

    public const INFO = 'info';

    public const DEBUG = 'debug';

    /**
     * Get all log level constants as an array.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::EMERGENCY,
            self::ALERT,
            self::CRITICAL,
            self::ERROR,
            self::WARNING,
            self::NOTICE,
            self::INFO,
            self::DEBUG,
        ];
    }
}
