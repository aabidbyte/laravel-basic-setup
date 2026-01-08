<?php

declare(strict_types=1);

namespace App\Services\ErrorHandling\Channels;

use App\Models\ErrorLog;
use Throwable;

/**
 * Database channel for error handling.
 *
 * Stores error details in the error_logs table for future
 * ticketing system integration and error tracking.
 */
class DatabaseChannel implements ChannelInterface
{
    /**
     * Store an error in the database.
     *
     * @param  Throwable  $e  The exception that occurred
     * @param  array<string, mixed>  $context  Error context
     */
    public function send(Throwable $e, array $context): void
    {
        ErrorLog::create([
            'reference_id' => $context['reference_id'],
            'exception_class' => $context['exception_class'],
            'message' => $context['message'],
            'stack_trace' => $context['stack_trace'],
            'url' => $context['url'] ?? null,
            'method' => $context['method'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'ip' => $context['ip'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
            'context' => $context['request_data'] ?? null,
        ]);
    }

    /**
     * Database channel should not be rate-limited.
     *
     * @return bool Always false - all errors should be stored
     */
    public function shouldRateLimit(): bool
    {
        return false;
    }
}
