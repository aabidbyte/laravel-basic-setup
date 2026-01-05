<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('messages.notifications.user_activated.subject', ['name' => $this->activatedUser->name]))
            ->greeting(__('messages.notifications.user_activated.greeting'))
            ->line(__('messages.notifications.user_activated.line1', ['name' => $this->activatedUser->name]))
            ->line(__('messages.notifications.user_activated.line2'))
            ->action(__('messages.notifications.user_activated.action'), route('users.show', $this->activatedUser->uuid))
            ->salutation(__('messages.notifications.user_activated.salutation', ['app' => config('app.name')]));
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => (__('messages.notifications.user_activated.title', ['name' => $this->activatedUser->name])),
            'subtitle' => (__('messages.notifications.user_activated.subtitle')),
            'content' => (__('messages.notifications.user_activated.content', [
                'name' => $this->activatedUser->name,
                'email' => $this->activatedUser->email ?? __('messages.common.no_email'),
            ])),
            'type' => 'success',
            'link' => route('users.show', $this->activatedUser->uuid),
            'activated_user_id' => $this->activatedUser->id,
            'activated_user_uuid' => $this->activatedUser->uuid,
        ];
    }
}
