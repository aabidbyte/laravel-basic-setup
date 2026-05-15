<?php

declare(strict_types=1);

namespace App\Events\Notifications;

use App\Events\Base\BaseEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class DatabaseNotificationChanged extends BaseEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

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
