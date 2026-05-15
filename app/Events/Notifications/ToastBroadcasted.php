<?php

declare(strict_types=1);

namespace App\Events\Notifications;

use App\Events\Base\BaseEvent;
use App\Services\Notifications\ToastPayload;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ToastBroadcasted extends BaseEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ToastPayload $payload,
        public string $channel,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        // Session channels are public (session ID acts as security mechanism)
        // All other channels are private (require authentication)
        if (\str_starts_with($this->channel, 'public-notifications.session.')) {
            return [
                new Channel($this->channel),
            ];
        }

        return [
            new PrivateChannel($this->channel),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'toast.received';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload->toArray();
    }
}
