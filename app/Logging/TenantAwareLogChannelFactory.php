<?php

namespace App\Logging;

use App\Constants\Logging\LogLevels;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class TenantAwareLogChannelFactory
{
    /**
     * Create a tenant-aware file logger that can behave as a single or daily channel.
     */
    public function __invoke(array $config): Logger
    {
        $path = $config['path'] ?? storage_path('logs/laravel.log');
        $level = $config['level'] ?? LogLevels::DEBUG;
        $days = $config['days'] ?? 14;
        $logger = new Logger($config['name'] ?? 'custom');

        $handler = ($config['daily'] ?? false)
            ? new TenantAwareRotatingFileHandler($path, $days, $level)
            : new TenantAwareStreamHandler($path, $level);

        $logger->pushHandler($handler);
        $logger->pushProcessor(new PsrLogMessageProcessor());

        return $logger;
    }
}
