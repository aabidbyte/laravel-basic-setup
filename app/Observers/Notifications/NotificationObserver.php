<?php

namespace App\Observers\Notifications;

use App\Events\Notifications\DatabaseNotificationChanged;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class NotificationObserver
{
    public function created(Notification $notification): void
    {
        $this->broadcastChange($notification, 'created');
    }

    public function updated(Notification $notification): void
    {
        $this->broadcastChange($notification, 'updated');
    }

    public function deleted(Notification $notification): void
    {
        $this->broadcastChange($notification, 'deleted');
    }

    public function restored(Notification $notification): void
    {
        $this->broadcastChange($notification, 'restored');
    }

    public function forceDeleted(Notification $notification): void
    {
        $this->broadcastChange($notification, 'forceDeleted');
    }

    protected function broadcastChange(Notification $notification, string $action): void
    {
        $userUuid = $notification->notifiable?->uuid;
        if (! $userUuid) {
            return;
        }

        $event = new DatabaseNotificationChanged(
            userUuid: $userUuid,
            notificationId: (string) $notification->id,
            action: $action,
        );

        if (\method_exists(DB::connection(), 'afterCommit')) {
            DB::afterCommit(fn () => event($event));
        } else {
            event($event);
        }
    }
}
