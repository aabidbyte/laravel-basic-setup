<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a user activates their account.
 *
 * Sent to:
 * - The user who created the new user
 * - Admins of the new user's teams (with deduplication)
 */
class UserActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  User  $activatedUser  The user who just activated their account
     */
    public function __construct(
        public readonly User $activatedUser,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): \Illuminate\Contracts\Mail\Mailable
    {
        return \App\Services\Mail\MailBuilder::make()
            ->to($notifiable)
            ->template('User Activated', [
                'user' => $notifiable,
                'activated_user' => $this->activatedUser,
            ], [
                'action_url' => route('users.show', $this->activatedUser->uuid),
            ])
            ->getMailable();
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titleKey' => 'messages.notifications.user_activated.title',
            'titleParams' => ['name' => $this->activatedUser->name],
            'subtitleKey' => 'messages.notifications.user_activated.subtitle',
            'contentStorable' => [
                'type' => 'string', // NotificationContent::TYPE_STRING
                'content' => [
                    'key' => 'messages.notifications.user_activated.content',
                    'params' => [
                        'name' => $this->activatedUser->name,
                        'email' => $this->activatedUser->email ?? __('messages.common.no_email'), // Immediate translation for fallback value inside param is okay, or we could support nested? For now this is fine.
                    ],
                ],
            ],
            'type' => 'success',
            'link' => route('users.show', $this->activatedUser->uuid),
            'activated_user_id' => $this->activatedUser->id,
            'activated_user_uuid' => $this->activatedUser->uuid,
        ];
    }
}
