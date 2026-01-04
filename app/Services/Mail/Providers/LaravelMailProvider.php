<?php

declare(strict_types=1);

namespace App\Services\Mail\Providers;

use App\Models\MailSettings;
use App\Services\Mail\Contracts\MailProviderContract;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Laravel Mail provider implementation.
 *
 * Uses Laravel's built-in mail system with dynamic configuration.
 * Supports SMTP and other Laravel-native transports.
 */
class LaravelMailProvider implements MailProviderContract
{
    /**
     * Send a mailable using Laravel's mail system.
     *
     * @param  Mailable  $mailable  The mailable to send
     * @param  MailSettings|null  $settings  Optional mail settings to use
     * @return bool True if the mail was sent successfully
     */
    public function send(Mailable $mailable, ?MailSettings $settings = null): bool
    {
        if ($settings !== null) {
            // Configure dynamic mailer
            $this->configureDynamicMailer($settings);
            Mail::mailer('dynamic')->send($mailable);
        } else {
            // Use default mailer
            Mail::send($mailable);
        }

        return true;
    }

    /**
     * Get the transport name for this provider.
     *
     * @return string The transport name
     */
    public function getTransportName(): string
    {
        return 'smtp';
    }

    /**
     * Build the mailer configuration array from settings.
     *
     * @param  MailSettings  $settings  The mail settings
     * @return array<string, mixed> The mailer configuration
     */
    public function buildConfig(MailSettings $settings): array
    {
        return [
            'transport' => $settings->provider,
            'host' => $settings->host,
            'port' => $settings->port,
            'encryption' => $settings->encryption,
            'username' => $settings->username,
            'password' => $settings->password,
            'timeout' => null,
            'local_domain' => parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST),
        ];
    }

    /**
     * Configure a dynamic mailer at runtime.
     *
     * @param  MailSettings  $settings  The mail settings to use
     */
    protected function configureDynamicMailer(MailSettings $settings): void
    {
        $config = $this->buildConfig($settings);

        // Set the dynamic mailer configuration
        Config::set('mail.mailers.dynamic', $config);

        // Set the from address if specified
        if ($settings->from_address) {
            Config::set('mail.from.address', $settings->from_address);
            Config::set('mail.from.name', $settings->from_name ?? config('app.name'));
        }
    }
}
