<?php

namespace App\Notifications\Auth;

use App\Services\Mail\MailBuilder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage|Mailable
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $url = $this->resetUrl($notifiable);
        $count = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        // Use MailBuilder with Database Template
        return MailBuilder::make()
            ->to($notifiable)
            ->template('Password Reset', [
                'user' => $notifiable,
            ], [
                'reset_url' => $url,
                'count' => $count,
            ])
            ->getMailable();
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'identifier' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
