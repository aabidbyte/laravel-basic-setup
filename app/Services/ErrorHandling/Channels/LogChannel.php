<?php

declare(strict_types=1);

namespace App\Services\ErrorHandling\Channels;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Log file channel for error handling.
 *
 * Logs error details to a configured log channel.
 * Uses Laravel's logging system with structured context.
 */
class LogChannel implements ChannelInterface
{
    /**
     * Send an error notification to the log.
     *
     * @param  Throwable  $e  The exception that occurred
     * @param  array<string, mixed>  $context  Error context
     */
    public function send(Throwable $e, array $context): void
    {
        $logChannel = config('error-handling.channels.log.channel', 'error');

        Log::channel($logChannel)->error(
            \sprintf('[%s] %s: %s', $context['reference_id'], class_basename($e), $e->getMessage()),
            [
                'reference_id' => $context['reference_id'],
                'exception_class' => $context['exception_class'],
                'file' => $context['file'] ?? null,
                'line' => $context['line'] ?? null,
                'url' => $context['url'] ?? null,
                'method' => $context['method'] ?? null,
                'user_uuid' => $context['user_uuid'] ?? null,
                'ip' => $context['ip'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
                'trace' => $this->formatTrace($e),
            ],
        );
    }

    /**
     * Log channel should not be rate-limited.
     *
     * @return bool Always false - logs should capture all errors
     */
    public function shouldRateLimit(): bool
    {
        return false;
    }

    /**
     * Format the exception trace for logging.
     *
     * @param  Throwable  $e  The exception
     * @return array<int, array<string, mixed>> Formatted trace
     */
    protected function formatTrace(Throwable $e): array
    {
        $trace = [];
        $maxFrames = 10;

        foreach (array_slice($e->getTrace(), 0, $maxFrames) as $frame) {
            $trace[] = [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
                'class' => $frame['class'] ?? null,
            ];
        }

        return $trace;
    }
}
