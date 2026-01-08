<?php

declare(strict_types=1);

namespace App\Services\ErrorHandling\Channels;

use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Email notification channel for error handling.
 *
 * Sends error details to configured email recipients.
 * Supports both queued (async) and direct sending.
 */
class EmailChannel implements ChannelInterface
{
    /**
     * Send an error notification via email.
     *
     * @param  Throwable  $e  The exception that occurred
     * @param  array<string, mixed>  $context  Error context
     */
    public function send(Throwable $e, array $context): void
    {
        $recipients = $this->getRecipients();

        if (empty($recipients)) {
            return;
        }

        $shouldQueue = config('error-handling.channels.email.queue', true);

        $mailable = $this->buildMailable($e, $context);

        if ($shouldQueue) {
            Mail::to($recipients)->queue($mailable);
        } else {
            Mail::to($recipients)->send($mailable);
        }
    }

    /**
     * Email notifications should be rate-limited.
     *
     * @return bool Always true - prevents email spam
     */
    public function shouldRateLimit(): bool
    {
        return true;
    }

    /**
     * Get the list of email recipients.
     *
     * @return array<string> List of email addresses
     */
    protected function getRecipients(): array
    {
        $recipientString = config('error-handling.channels.email.recipients', '');

        if (empty($recipientString)) {
            return [];
        }

        return array_map('trim', explode(',', $recipientString));
    }

    /**
     * Build the error notification mailable.
     *
     * @param  Throwable  $e  The exception
     * @param  array<string, mixed>  $context  Error context
     * @return \Illuminate\Mail\Mailable The mailable instance
     */
    protected function buildMailable(Throwable $e, array $context): \Illuminate\Mail\Mailable
    {
        return new \Illuminate\Mail\Mailable(function ($message) use ($e, $context) {
            $appName = config('app.name', 'Laravel');
            $environment = app()->environment();

            $message->subject(sprintf(
                '[%s] Error: %s (%s)',
                $appName,
                $context['reference_id'],
                $environment,
            ));

            $message->html($this->buildHtmlBody($e, $context));
        });
    }

    /**
     * Build the HTML email body.
     *
     * @param  Throwable  $e  The exception
     * @param  array<string, mixed>  $context  Error context
     * @return string HTML content
     */
    protected function buildHtmlBody(Throwable $e, array $context): string
    {
        $appName = config('app.name', 'Laravel');
        $environment = app()->environment();

        // Extract values to avoid null coalescing in HEREDOC
        $referenceId = $context['reference_id'];
        $exceptionClass = $context['exception_class'];
        $message = $this->escapeHtml($context['message']);
        $url = $context['url'] ?? 'N/A';
        $userUuid = $context['user_uuid'] ?? 'Guest';
        $ip = $context['ip'] ?? 'Unknown';
        $file = $context['file'] ?? 'Unknown';
        $line = $context['line'] ?? 0;
        $stackTrace = $this->escapeHtml($this->truncateStackTrace($context['stack_trace'] ?? ''));

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
                .field { margin-bottom: 15px; }
                .field-label { font-weight: bold; color: #6c757d; font-size: 12px; text-transform: uppercase; }
                .field-value { margin-top: 5px; }
                .code { background: #e9ecef; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 13px; overflow-x: auto; }
                .reference { background: #343a40; color: white; padding: 10px 15px; border-radius: 4px; font-family: monospace; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2 style="margin: 0;">⚠️ Error in {$appName}</h2>
                    <p style="margin: 10px 0 0 0; opacity: 0.9;">Environment: {$environment}</p>
                </div>
                <div class="content">
                    <div class="field">
                        <div class="field-label">Reference ID</div>
                        <div class="field-value"><span class="reference">{$referenceId}</span></div>
                    </div>
                    <div class="field">
                        <div class="field-label">Exception</div>
                        <div class="field-value">{$exceptionClass}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">Message</div>
                        <div class="field-value code">{$message}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">URL</div>
                        <div class="field-value">{$url}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">User</div>
                        <div class="field-value">{$userUuid}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">IP Address</div>
                        <div class="field-value">{$ip}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">File</div>
                        <div class="field-value code">{$file}:{$line}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">Stack Trace</div>
                        <div class="field-value"><pre class="code" style="max-height: 300px; overflow-y: auto;">{$stackTrace}</pre></div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Escape HTML special characters.
     *
     * @param  string  $text  Text to escape
     * @return string Escaped text
     */
    protected function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Truncate stack trace to reasonable length.
     *
     * @param  string  $stackTrace  Full stack trace
     * @return string Truncated stack trace
     */
    protected function truncateStackTrace(string $stackTrace): string
    {
        $lines = explode("\n", $stackTrace);
        $maxLines = 20;

        if (count($lines) <= $maxLines) {
            return $stackTrace;
        }

        $truncated = array_slice($lines, 0, $maxLines);
        $truncated[] = sprintf('... and %d more lines', count($lines) - $maxLines);

        return implode("\n", $truncated);
    }
}
