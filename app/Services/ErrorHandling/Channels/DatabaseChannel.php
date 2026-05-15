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
        $payload = $this->payload($context);

        ErrorLog::query()->create($payload);

        if (($context['tenant_id'] ?? null) !== null && $this->currentErrorLogConnection() !== 'central') {
            $this->storeCentralMirror($payload);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function payload(array $context): array
    {
        return [
            'reference_id' => $context['reference_id'],
            'exception_class' => $context['exception_class'],
            'message' => $context['message'],
            'stack_trace' => $context['stack_trace'],
            'url' => $context['url'] ?? null,
            'method' => $context['method'] ?? null,
            'tenant_id' => $context['tenant_id'] ?? null,
            'tenant_name' => $context['tenant_name'] ?? null,
            'tenant_domain' => $context['tenant_domain'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'user_uuid' => $context['user_uuid'] ?? null,
            'actor_type' => $context['actor_type'] ?? null,
            'actor_name' => $context['actor_name'] ?? null,
            'actor_email' => $context['actor_email'] ?? null,
            'impersonator_id' => $context['impersonator_id'] ?? null,
            'impersonator_name' => $context['impersonator_name'] ?? null,
            'impersonator_email' => $context['impersonator_email'] ?? null,
            'ip' => $context['ip'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
            'runtime_context' => $context['runtime_context'] ?? null,
            'command' => $context['command'] ?? null,
            'job_id' => $context['job_id'] ?? null,
            'context' => $context['request_data'] ?? null,
        ];
    }

    protected function currentErrorLogConnection(): string
    {
        return ErrorLog::query()->getConnection()->getName();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function storeCentralMirror(array $payload): void
    {
        ErrorLog::on('central')->firstOrCreate(
            ['reference_id' => $payload['reference_id']],
            $payload,
        );
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
