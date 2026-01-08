<?php

declare(strict_types=1);

namespace App\Services\ErrorHandling\Channels;

use Throwable;

/**
 * Interface for error notification channels.
 *
 * Each channel implementation is responsible for sending error
 * notifications through a specific medium (toast, email, Slack, etc.).
 */
interface ChannelInterface
{
    /**
     * Send an error notification through this channel.
     *
     * @param  Throwable  $e  The exception that occurred
     * @param  array<string, mixed>  $context  Error context including reference_id, url, user info, etc.
     */
    public function send(Throwable $e, array $context): void;

    /**
     * Check if this channel should be rate-limited.
     *
     * @return bool True if this channel should respect rate limiting
     */
    public function shouldRateLimit(): bool;
}
