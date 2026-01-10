<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Security notification email sent to the old email address when an email change is requested.
 *
 * This email warns the user about the email change and instructs them to contact support
 * if they did not initiate the change.
 */
class EmailChangeSecurityMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  User  $user  The user whose email is being changed
     * @param  string  $newEmail  The new email address being requested
     */
    public function __construct(
        public readonly User $user,
        public readonly string $newEmail,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.email_change_security.subject', ['app' => config('app.name')]),
        );
    }

    /**
     * Get the locale for this mailable.
     */
    public function locale($locale = null): static
    {
        return parent::locale($locale ?? config('app.locale'));
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-security',
            with: [
                'user' => $this->user,
                'newEmail' => $this->newEmail,
                'appName' => config('app.name'),
                'supportEmail' => config('mail.support_email', config('mail.from.address')),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
