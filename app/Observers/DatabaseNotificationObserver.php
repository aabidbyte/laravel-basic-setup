<?php

namespace App\Observers;

use App\Events\DatabaseNotificationChanged;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationObserver
{
    public function created(DatabaseNotification $notification): void
    {
        $this->broadcastChange($notification, 'created');
    }

    public function updated(DatabaseNotification $notification): void
    {
        $this->broadcastChange($notification, 'updated');
    }

    public function deleted(DatabaseNotification $notification): void
    {
        $this->broadcastChange($notification, 'deleted');
    }

    public function restored(DatabaseNotification $notification): void
    {
        $this->broadcastChange($notification, 'restored');
    }

    public function forceDeleted(DatabaseNotification $notification): void
    {
        $this->broadcastChange($notification, 'forceDeleted');
    }

    protected function broadcastChange(DatabaseNotification $notification, string $action): void
    {
        $userUuid = $notification->notifiable?->uuid;
        if (! $userUuid) {
            return;
        }

        event(new DatabaseNotificationChanged(
            userUuid: $userUuid,
            notificationId: (string) $notification->id,
            action: $action,
        ));
    }
}
