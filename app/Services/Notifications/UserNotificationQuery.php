<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserNotificationQuery
{
    public function forUser(User $user): Builder
    {
        return Notification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey());
    }

    public function unreadCount(User $user): int
    {
        return (clone $this->forUser($user))
            ->unread()
            ->count();
    }

    public function allSorted(User $user): Collection
    {
        return $this->forUser($user)
            ->latest()
            ->get()
            ->sortBy([['created_at', 'desc'], ['read_at', 'asc']]);
    }

    /**
     * @return array{totalCount: int, unreadCount: int}
     */
    public function stats(User $user): array
    {
        $stats = $this->forUser($user)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread_count')
            ->first();

        return [
            'totalCount' => (int) ($stats->total_count ?? 0),
            'unreadCount' => (int) ($stats->unread_count ?? 0),
        ];
    }

    public function find(User $user, string $notificationId): ?Notification
    {
        return $this->forUser($user)->find($notificationId);
    }
}
