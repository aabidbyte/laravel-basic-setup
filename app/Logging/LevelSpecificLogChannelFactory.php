<?php

namespace App\Logging;

use App\Constants\LogLevels;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class LevelSpecificLogChannelFactory
{
    /**
     * Create a Monolog logger instance with exact level filtering and daily rotation.
     */
    public function __invoke(array $config): Logger
    {
        $level = $this->getLevelConstant($config['level'] ?? LogLevels::DEBUG);
        $path = $config['path'] ?? storage_path('logs/laravel.log');
        $days = $config['days'] ?? 14;

        $logger = new Logger($config['name'] ?? 'custom');

        $rotatingHandler = new RotatingFileHandler($path, $days, $level);
        $filteredHandler = new FilterHandler(
            $rotatingHandler,
            $level,
            $level
        );

        $logger->pushHandler($filteredHandler);
        $logger->pushProcessor(new PsrLogMessageProcessor);

        return $logger;
    }

    /**
     * Convert log level string to Monolog constant.
     */
    private function getLevelConstant(string $level): int
    {
        return match ($level) {
            'emergency' => Logger::EMERGENCY,
            'alert' => Logger::ALERT,
            'critical' => Logger::CRITICAL,
            'error' => Logger::ERROR,
            'warning' => Logger::WARNING,
            'notice' => Logger::NOTICE,
            'info' => Logger::INFO,
            'debug' => Logger::DEBUG,
            default => Logger::DEBUG,
        };
    }
}
