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
 * Verification email sent to the new email address when an email change is requested.
 *
 * Contains a verification link that the user must click to confirm the email change.
 */
class EmailChangeVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  User  $user  The user requesting the email change
     * @param  string  $verificationUrl  The URL to verify the email change
     * @param  int  $expiresInDays  Number of days until the link expires
     */
    public function __construct(
        public readonly User $user,
        public readonly string $verificationUrl,
        public readonly int $expiresInDays = 7,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.email_change_verification.subject', ['app' => config('app.name')]),
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
            view: 'emails.email-change-verification',
            with: [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'expiresInDays' => $this->expiresInDays,
                'appName' => config('app.name'),
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
