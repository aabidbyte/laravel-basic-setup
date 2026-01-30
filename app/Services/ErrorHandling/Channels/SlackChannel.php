<?php

declare(strict_types=1);

namespace App\Services\ErrorHandling\Channels;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Slack webhook notification channel for error handling.
 *
 * Sends error details to a configured Slack webhook URL.
 * Includes exception details, user info, and reference ID.
 */
class SlackChannel implements ChannelInterface
{
    /**
     * Send an error notification to Slack.
     *
     * @param  Throwable  $e  The exception that occurred
     * @param  array<string, mixed>  $context  Error context
     */
    public function send(Throwable $e, array $context): void
    {
        $webhookUrl = config('error-handling.channels.slack.webhook_url');

        if (empty($webhookUrl)) {
            return;
        }

        $payload = $this->buildPayload($e, $context);

        Http::timeout(5)->post($webhookUrl, $payload);
    }

    /**
     * Slack notifications should be rate-limited.
     *
     * @return bool Always true - prevents notification spam
     */
    public function shouldRateLimit(): bool
    {
        return true;
    }

    /**
     * Build the Slack message payload.
     *
     * @param  Throwable  $e  The exception
     * @param  array<string, mixed>  $context  Error context
     * @return array<string, mixed> Slack webhook payload
     */
    protected function buildPayload(Throwable $e, array $context): array
    {
        $environment = app()->environment();
        $appName = config('app.name', 'Laravel');

        return [
            'username' => config('error-handling.channels.slack.username', 'Error Handler'),
            'icon_emoji' => config('error-handling.channels.slack.emoji', ':rotating_light:'),
            'text' => \sprintf(':rotating_light: Error in *%s* (%s)', $appName, $environment),
            'attachments' => [
                [
                    'color' => 'danger',
                    'title' => \sprintf('Error: %s', $context['reference_id']),
                    'title_link' => $context['url'] ?? null,
                    'fields' => [
                        [
                            'title' => 'Exception',
                            'value' => class_basename($context['exception_class']),
                            'short' => true,
                        ],
                        [
                            'title' => 'Environment',
                            'value' => $environment,
                            'short' => true,
                        ],
                        [
                            'title' => 'Message',
                            'value' => $this->truncate($context['message'], 500),
                            'short' => false,
                        ],
                        [
                            'title' => 'URL',
                            'value' => $context['url'] ?? 'N/A',
                            'short' => true,
                        ],
                        [
                            'title' => 'Method',
                            'value' => $context['method'] ?? 'N/A',
                            'short' => true,
                        ],
                        [
                            'title' => 'User',
                            'value' => $context['user_uuid'] ?? 'Guest',
                            'short' => true,
                        ],
                        [
                            'title' => 'IP',
                            'value' => $context['ip'] ?? 'Unknown',
                            'short' => true,
                        ],
                        [
                            'title' => 'File',
                            'value' => \sprintf(
                                '%s:%d',
                                basename($context['file'] ?? 'unknown'),
                                $context['line'] ?? 0,
                            ),
                            'short' => false,
                        ],
                    ],
                    'footer' => \sprintf('Reference: %s', $context['reference_id']),
                    'ts' => now()->timestamp,
                ],
            ],
        ];
    }

    /**
     * Truncate a string to a maximum length.
     *
     * @param  string  $text  The text to truncate
     * @param  int  $maxLength  Maximum length
     * @return string Truncated text
     */
    protected function truncate(string $text, int $maxLength): string
    {
        if (\strlen($text) <= $maxLength) {
            return $text;
        }

        return \substr($text, 0, $maxLength - 3) . '...';
    }
}
