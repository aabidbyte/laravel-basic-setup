<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage|\Illuminate\Contracts\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $url = $this->resetUrl($notifiable);
        $count = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        // Use MailBuilder with Database Template
        return \App\Services\Mail\MailBuilder::make()
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
