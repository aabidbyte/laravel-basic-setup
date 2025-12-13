<?php

namespace App\Constants;

/**
 * Log channel constants for Laravel logging.
 *
 * CRITICAL RULE: All log channel names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for log channel names throughout the application.
 */
class LogChannels
{
    public const STACK = 'stack';

    public const SINGLE = 'single';

    public const DAILY = 'daily';

    public const EMERGENCY = 'emergency';

    public const ALERT = 'alert';

    public const CRITICAL = 'critical';

    public const ERROR = 'error';

    public const WARNING = 'warning';

    public const NOTICE = 'notice';

    public const INFO = 'info';

    public const DEBUG = 'debug';

    public const DEPRECATIONS = 'deprecations';

    public const SLACK = 'slack';

    public const PAPERTRAIL = 'papertrail';

    public const STDERR = 'stderr';

    public const SYSLOG = 'syslog';

    public const ERRORLOG = 'errorlog';

    public const NULL = 'null';

    /**
     * Get all level-specific log channel constants as an array.
     *
     * @return array<string>
     */
    public static function levelChannels(): array
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
