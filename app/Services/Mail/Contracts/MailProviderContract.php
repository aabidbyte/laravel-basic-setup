<?php

declare(strict_types=1);

namespace App\Services\Mail\Contracts;

use App\Models\MailSettings;
use Illuminate\Contracts\Mail\Mailable;

/**
 * Contract for mail provider implementations.
 *
 * Mail providers handle sending emails using specific transport configurations.
 * Each provider can use different credentials and transport mechanisms.
 */
interface MailProviderContract
{
    /**
     * Send a mailable using this provider.
     *
     * @param  Mailable  $mailable  The mailable to send
     * @param  MailSettings|null  $settings  Optional mail settings to use
     * @return bool True if the mail was sent successfully
     */
    public function send(Mailable $mailable, ?MailSettings $settings = null): bool;

    /**
     * Get the transport name for this provider.
     *
     * @return string The transport name (e.g., 'smtp', 'ses', 'postmark')
     */
    public function getTransportName(): string;

    /**
     * Build the mailer configuration array from settings.
     *
     * @param  MailSettings  $settings  The mail settings
     * @return array<string, mixed> The mailer configuration
     */
    public function buildConfig(MailSettings $settings): array;
}
