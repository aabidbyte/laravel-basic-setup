<?php

declare(strict_types=1);

namespace App\Services\ErrorHandling\Channels;

use App\Services\Notifications\NotificationBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

/**
 * Toast notification channel for error handling.
 *
 * Uses the existing NotificationBuilder to display error toasts.
 * Shows detailed errors in development, user-friendly messages in production.
 */
class ToastChannel implements ChannelInterface
{
    /**
     * Send an error notification as a toast.
     *
     * @param  Throwable  $e  The exception that occurred
     * @param  array<string, mixed>  $context  Error context
     */
    public function send(Throwable $e, array $context): void
    {
        $isProduction = $context['is_production'] ?? app()->isProduction();

        $title = __('errors.generic_title');

        if ($isProduction) {
            // Production: user-friendly message with reference ID
            $subtitle = __('errors.reference', ['id' => $context['reference_id']]);
            $content = __('errors.generic_message');
        } else {
            // Development: show full exception details
            $subtitle = $e->getMessage();
            $content = sprintf(
                '%s:%d',
                basename($e->getFile()),
                $e->getLine(),
            );
        }

        $notification = NotificationBuilder::make()
            ->title($title)
            ->subtitle($subtitle)
            ->content($content)
            ->sticky();

        // Use warning for authorization exceptions, error for everything else
        if ($e instanceof AuthorizationException) {
            $notification->warning();
        } else {
            $notification->error();
        }

        $notification->send();
    }

    /**
     * Toast notifications should not be rate-limited.
     *
     * @return bool Always false - users should always see error feedback
     */
    public function shouldRateLimit(): bool
    {
        return false;
    }
}
