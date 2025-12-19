<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabaseNotificationChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $userUuid,
        public string $notificationId,
        public string $action,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("private-notifications.user.{$this->userUuid}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.changed';
    }

    /**
     * @return array{notificationId: string, action: string}
     */
    public function broadcastWith(): array
    {
        return [
            'notificationId' => $this->notificationId,
            'action' => $this->action,
        ];
    }
}
