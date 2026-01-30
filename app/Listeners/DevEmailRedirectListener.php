<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Address;

/**
 * Development Email Redirect Listener.
 *
 * Intercepts all outgoing emails when MAIL_DEV_REDIRECT_ENABLED is true
 * and redirects them to configured addresses, preventing accidental
 * emails to real users during development.
 *
 * Configuration via .env:
 * - MAIL_DEV_REDIRECT_ENABLED=true
 * - MAIL_DEV_REDIRECT_TO=dev@example.com,admin@example.com
 * - MAIL_DEV_REDIRECT_TO_ROLES=superAdmin (optional, comma-separated roles to allow through)
 */
class DevEmailRedirectListener
{
    /**
     * Handle the event.
     */
    public function handle(MessageSending $event): void
    {
        // Only process if dev redirect is enabled
        if (! config('mail.dev_redirect.enabled', false)) {
            return;
        }

        $redirectTo = config('mail.dev_redirect.to', '');
        if (empty($redirectTo)) {
            Log::warning('DevEmailRedirect: Enabled but no redirect addresses configured');

            return;
        }

        $message = $event->message;
        $originalRecipients = $this->getRecipientEmails($message->getTo());

        // Check if any recipient should be allowed through (based on role)
        $allowedRoles = $this->getAllowedRoles();
        if (! empty($allowedRoles)) {
            $allowedRecipients = $this->filterAllowedRecipients($originalRecipients, $allowedRoles);
            if (\count($allowedRecipients) === \count($originalRecipients)) {
                // All recipients are allowed through
                return;
            }
        }

        // Store original recipients in header for debugging
        $message->getHeaders()->addTextHeader(
            'X-Original-To',
            \implode(', ', $originalRecipients),
        );

        // Clear all recipients
        $this->clearRecipients($message);

        // Set new redirect recipients
        $redirectEmails = array_map('trim', \explode(',', $redirectTo));
        foreach ($redirectEmails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message->addTo(new Address($email, 'Dev Redirect'));
            }
        }

        Log::info('DevEmailRedirect: Redirected email', [
            'original_to' => $originalRecipients,
            'redirected_to' => $redirectEmails,
            'subject' => $message->getSubject(),
        ]);
    }

    /**
     * Get email addresses from recipients.
     *
     * @param  iterable<Address>  $recipients
     * @return array<string>
     */
    protected function getRecipientEmails(iterable $recipients): array
    {
        $emails = [];
        foreach ($recipients as $recipient) {
            $emails[] = $recipient->getAddress();
        }

        return $emails;
    }

    /**
     * Get allowed roles from config.
     *
     * @return array<string>
     */
    protected function getAllowedRoles(): array
    {
        $roles = config('mail.dev_redirect.allowed_roles', '');
        if (empty($roles)) {
            return [];
        }

        return array_map('trim', \explode(',', $roles));
    }

    /**
     * Filter recipients that are allowed through based on their roles.
     *
     * @param  array<string>  $emails
     * @param  array<string>  $allowedRoles
     * @return array<string>
     */
    protected function filterAllowedRecipients(array $emails, array $allowedRoles): array
    {
        return array_filter($emails, function ($email) use ($allowedRoles) {
            $user = User::where('email', $email)->first();
            if ($user === null) {
                return false;
            }

            return $user->hasAnyRole($allowedRoles);
        });
    }

    /**
     * Clear all recipients from the message.
     *
     * Note: Symfony\Component\Mime\Email stores recipients in headers, not properties.
     * We need to use getHeaders() to properly clear them.
     */
    protected function clearRecipients(\Symfony\Component\Mime\Email $message): void
    {
        $headers = $message->getHeaders();

        // Remove To, Cc, and Bcc headers
        if ($headers->has('to')) {
            $headers->remove('to');
        }
        if ($headers->has('cc')) {
            $headers->remove('cc');
        }
        if ($headers->has('bcc')) {
            $headers->remove('bcc');
        }
    }
}
