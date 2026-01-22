<?php

namespace App\Notifications\Auth;

use App\Services\Mail\MailBuilder;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @return \Illuminate\Contracts\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        // Use default built-in URL generation logic from BaseVerifyEmail
        // but we need to pass it to our template.
        // BaseVerifyEmail generates the URL inside toMail method usually?
        // No, BaseVerifyEmail::toMail calls $this->verificationUrl($notifiable).
        $url = $this->verificationUrl($notifiable);

        return MailBuilder::make()
            ->to($notifiable)
            ->template('Verify Email', [
                'user' => $notifiable,
            ], [
                'verification_url' => $url,
            ])
            ->getMailable();
    }
}
