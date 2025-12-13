<?php

use App\Constants\LogChannels;
use App\Constants\LogLevels;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 * Create a level-specific log channel configuration with exact level filtering.
 *
 * @param  string  $channel
 * @param  string  $level
 * @return array<string, mixed>
 */
$createLevelSpecificChannel = function (string $channel, string $level): array {
    return [
        'driver' => 'custom',
        'via' => \App\Logging\LevelSpecificLogChannelFactory::class,
        'name' => $channel,
        'path' => storage_path("logs/{$channel}/laravel.log"),
        'level' => $level,
        'days' => env('LOG_DAILY_DAYS', 14),
    ];
};

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', LogChannels::STACK),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', LogChannels::DEPRECATIONS),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        LogChannels::STACK => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', implode(',', LogChannels::levelChannels()))),
            'ignore_exceptions' => false,
        ],

        LogChannels::SINGLE => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', LogLevels::DEBUG),
            'replace_placeholders' => true,
        ],

        LogChannels::DAILY => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', LogLevels::DEBUG),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        // Log channels separated by level - each level has its own folder with daily rotation
        // Each channel only logs messages of its exact level using FilterHandler
        LogChannels::EMERGENCY => $createLevelSpecificChannel(LogChannels::EMERGENCY, LogLevels::EMERGENCY),

        LogChannels::ALERT => $createLevelSpecificChannel(LogChannels::ALERT, LogLevels::ALERT),

        LogChannels::CRITICAL => $createLevelSpecificChannel(LogChannels::CRITICAL, LogLevels::CRITICAL),

        LogChannels::ERROR => $createLevelSpecificChannel(LogChannels::ERROR, LogLevels::ERROR),

        LogChannels::WARNING => $createLevelSpecificChannel(LogChannels::WARNING, LogLevels::WARNING),

        LogChannels::NOTICE => $createLevelSpecificChannel(LogChannels::NOTICE, LogLevels::NOTICE),

        LogChannels::INFO => $createLevelSpecificChannel(LogChannels::INFO, LogLevels::INFO),

        LogChannels::DEBUG => $createLevelSpecificChannel(LogChannels::DEBUG, LogLevels::DEBUG),

        LogChannels::DEPRECATIONS => $createLevelSpecificChannel(LogChannels::DEPRECATIONS, LogLevels::WARNING),

        LogChannels::SLACK => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', LogLevels::CRITICAL),
            'replace_placeholders' => true,
        ],

        LogChannels::PAPERTRAIL => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', LogLevels::DEBUG),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        LogChannels::STDERR => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', LogLevels::DEBUG),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        LogChannels::SYSLOG => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', LogLevels::DEBUG),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        LogChannels::ERRORLOG => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', LogLevels::DEBUG),
            'replace_placeholders' => true,
        ],

        LogChannels::NULL => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

    ],

];
