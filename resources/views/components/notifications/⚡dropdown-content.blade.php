<?php

use App\Livewire\Bases\LivewireBaseComponent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

new class extends LivewireBaseComponent {
    /**
     * Refresh the notifications list.
     */
    public int $limit = 10;

    /**
     * Load more notifications.
     */
    public function loadMore(): void
    {
        $this->limit += 10;
    }

    /**
     * Get all sorted notifications (cached for request).
     */
    public function getAllSortedNotificationsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }

        return $user
            ->notifications()
            ->latest()
            ->get()
            ->sortBy([['created_at', 'desc'], ['read_at', 'asc']]);
    }

    /**
     * Get the user's notifications (limited).
     */
    public function getNotificationsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->allSortedNotifications->values()->take($this->limit);
    }

    /**
     * Check if there are more notifications to load.
     */
    public function getHasMoreProperty(): bool
    {
        return $this->allSortedNotifications->count() > $this->limit;
    }

    /**
     * Get formatted notifications for display.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFormattedNotificationsProperty(): array
    {
        return $this->notifications
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->resolved_title,
                    'subtitle' => $notification->resolved_subtitle,
                    'type' => $notification->notification_type,
                    'link' => $notification->notification_link,
                    'isRead' => $notification->is_read,
                    'createdAt' => $notification->created_at,
                    'iconName' => $this->getIconNameForType($notification->notification_type),
                    'iconClass' => $this->getIconClassForType($notification->notification_type),
                    'linkClass' => $this->getLinkClass($notification->is_read),
                    'hasLink' => $notification->has_link,
                    'wrapperClass' => $this->getLinkClass($notification->is_read) . ($notification->has_link ? '' : ' cursor-default'),
                    'markAsReadAction' => !$notification->is_read ? "markAsRead('{$notification->id}')" : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get the icon name for a notification type.
     */
    protected function getIconNameForType(string $type): string
    {
        return match ($type) {
            'success' => 'check-circle',
            'info' => 'information-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'x-circle',
            default => 'bell',
        };
    }

    /**
     * Get the icon CSS class for a notification type.
     */
    protected function getIconClassForType(string $type): string
    {
        return match ($type) {
            'success' => 'h-4 w-4 text-success',
            'info' => 'h-4 w-4 text-info',
            'warning' => 'h-4 w-4 text-warning',
            'error' => 'h-4 w-4 text-error',
            default => 'h-4 w-4 text-base-content',
        };
    }

    /**
     * Get the link CSS class based on read status.
     */
    protected function getLinkClass(bool $isRead): string
    {
        return $isRead ? '' : 'font-semibold';
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all visible notifications as read.
     * Uses direct query to avoid lazy loading issues when called from JavaScript.
     */
    public function markVisibleAsRead(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        // Get the IDs of the visible notifications (based on current limit)
        $visibleNotificationIds = $user
            ->notifications()
            ->latest()
            ->get()
            ->sortBy([['read_at', 'asc'], ['created_at', 'desc']])
            ->take($this->limit)
            ->pluck('id')
            ->toArray();

        // Mark only unread notifications as read in a single query
        if (!empty($visibleNotificationIds)) {
            $user
                ->unreadNotifications()
                ->whereIn('id', $visibleNotificationIds)
                ->update(['read_at' => now()]);
        }
    }

    /**
     * Get the unread notification count.
     */
    public function getUnreadCountProperty(): int
    {
        $user = Auth::user();
        if (!$user) {
            return 0;
        }

        return $user->unreadNotifications()->count();
    }

    /**
     * After component renders, emit the unread count to the trigger.
     */
    public function rendered(View $view): void
    {
        $this->dispatch('notification-dropdown:count-updated', count: $this->unreadCount);
    }
}; ?>

<div x-data="notificationDropdownContent($wire)"
     x-on:notifications-changed.window="$wire.$refresh()"
     wire:key="notification-dropdown-content-{{ Auth::user()?->uuid ?? 'guest' }}">
    @forelse($this->formattedNotifications as $notification)
        <div x-intersect.once="delayedMarkAsRead('{{ $notification['id'] }}')">
            @if ($notification['hasLink'])
                @if ($notification['markAsReadAction'])
                    <a href="{{ $notification['link'] }}"
                       class="{{ $notification['wrapperClass'] }}"
                       wire:navigate
                       wire:click="{{ $notification['markAsReadAction'] }}">
                        <x-notifications.notification-item :iconName="$notification['iconName']"
                                                           :iconClass="$notification['iconClass']"
                                                           :title="$notification['title']"
                                                           :subtitle="$notification['subtitle']"
                                                           :createdAt="$notification['createdAt']"
                                                           :isRead="$notification['isRead']"></x-notifications.notification-item>
                    </a>
                @else
                    <a href="{{ $notification['link'] }}"
                       class="{{ $notification['wrapperClass'] }}"
                       wire:navigate>
                        <x-notifications.notification-item :iconName="$notification['iconName']"
                                                           :iconClass="$notification['iconClass']"
                                                           :title="$notification['title']"
                                                           :subtitle="$notification['subtitle']"
                                                           :createdAt="$notification['createdAt']"
                                                           :isRead="$notification['isRead']"></x-notifications.notification-item>
                    </a>
                @endif
            @else
                @if ($notification['markAsReadAction'])
                    <div class="{{ $notification['wrapperClass'] }}"
                         wire:click="{{ $notification['markAsReadAction'] }}">
                        <x-notifications.notification-item :iconName="$notification['iconName']"
                                                           :iconClass="$notification['iconClass']"
                                                           :title="$notification['title']"
                                                           :subtitle="$notification['subtitle']"
                                                           :createdAt="$notification['createdAt']"
                                                           :isRead="$notification['isRead']"></x-notifications.notification-item>
                    </div>
                @else
                    <div class="{{ $notification['wrapperClass'] }}">
                        <x-notifications.notification-item :iconName="$notification['iconName']"
                                                           :iconClass="$notification['iconClass']"
                                                           :title="$notification['title']"
                                                           :subtitle="$notification['subtitle']"
                                                           :createdAt="$notification['createdAt']"
                                                           :isRead="$notification['isRead']"></x-notifications.notification-item>
                    </div>
                @endif
            @endif
        </div>
    @empty
        <div class="py-4 text-center text-sm opacity-60">
            {{ __('notifications.empty') }}
        </div>
    @endforelse

    @if ($this->hasMore)
        <div x-intersect.full="$wire.loadMore()"
             class="flex justify-center p-4">
            <x-ui.loading wire:loading
                          class="loading-md opacity-50" />
        </div>
    @endif
</div>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('notificationDropdownContent', ($wire) => ({
                    init() {
                        // Optimized: No global listeners needed for basic intersection logic
                    },

                    delayedMarkAsRead(notificationId, delayMs = 2000) {
                        setTimeout(() => {
                            if ($wire) {
                                $wire.markAsRead(notificationId);
                            }
                        }, delayMs);
                    }
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
