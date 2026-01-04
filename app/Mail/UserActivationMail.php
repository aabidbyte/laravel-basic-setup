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
 * User activation email.
 *
 * Sent when a new user is created and needs to activate their account.
 * Contains a secure activation link that allows the user to set their password.
 */
class UserActivationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  User  $user  The user to activate
     * @param  string  $activationUrl  The activation URL
     * @param  int  $expiresInDays  Number of days until the link expires
     */
    public function __construct(
        public readonly User $user,
        public readonly string $activationUrl,
        public readonly int $expiresInDays = 7,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.activation.subject', ['app' => config('app.name')]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.activation',
            with: [
                'user' => $this->user,
                'activationUrl' => $this->activationUrl,
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
