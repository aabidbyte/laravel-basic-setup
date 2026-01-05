<?php

use App\Events\Notifications\DatabaseNotificationChanged;
use App\Livewire\BasePageComponent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

new class extends BasePageComponent {
    public ?string $pageTitle = 'ui.pages.notifications';

    public int $visibleCount = 10;

    public function refreshNotifications(): void
    {
        $this->dispatch('$refresh');
    }

    public function loadMore(): void
    {
        $this->visibleCount += 10;
    }

    #[Computed]
    public function notificationStats(): array
    {
        $user = Auth::user();

        // Get both counts in a single optimized query using the relationship's query builder
        $stats = $user->notifications()->getQuery()->selectRaw('COUNT(*) as total_count')->selectRaw('SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread_count')->first();

        return [
            'totalCount' => (int) ($stats->total_count ?? 0),
            'unreadCount' => (int) ($stats->unread_count ?? 0),
        ];
    }

    #[Computed]
    public function notifications(): \Illuminate\Support\Collection
    {
        return Auth::user()
            ->notifications()
            ->latest()
            ->take($this->visibleCount)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;
                $type = $data['type'] ?? 'classic';
                $icon = $this->getNotificationIcon($type);

                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'subtitle' => $data['subtitle'] ?? null,
                    'content' => $data['content'] ?? null,
                    'type' => $type,
                    'link' => $data['link'] ?? null,
                    'isRead' => $notification->read_at !== null,
                    'readAt' => $notification->read_at,
                    'createdAt' => $notification->created_at,
                    'icon' => $icon,
                ];
            });
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->notificationStats['totalCount'];
    }

    #[Computed]
    public function unreadCount(): int
    {
        return $this->notificationStats['unreadCount'];
    }

    #[Computed]
    public function remainingCount(): int
    {
        return max(0, $this->totalCount - $this->visibleCount);
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();

        $user = Auth::user();
        event(new DatabaseNotificationChanged(userUuid: $user->uuid, notificationId: '*', action: 'bulkUpdated'));
    }

    public function delete(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->delete();
        }
    }

    public function clearAll(): void
    {
        $user = Auth::user();

        // Bulk delete doesn't fire model events; delete IDs then broadcast a single refresh event.
        $user->notifications()->delete();

        event(new DatabaseNotificationChanged(userUuid: $user->uuid, notificationId: '*', action: 'bulkDeleted'));
    }

    /**
     * Get icon configuration for notification type.
     *
     * @param  string  $type  Notification type
     * @return array{name: string, class: string}
     */
    public function getNotificationIcon(string $type): array
    {
        return match ($type) {
            'success' => ['name' => 'check-circle', 'class' => 'h-6 w-6 text-success'],
            'info' => ['name' => 'information-circle', 'class' => 'h-6 w-6 text-info'],
            'warning' => ['name' => 'exclamation-triangle', 'class' => 'h-6 w-6 text-warning'],
            'error' => ['name' => 'x-circle', 'class' => 'h-6 w-6 text-error'],
            default => ['name' => 'bell', 'class' => 'h-6 w-6 text-base-content'],
        };
    }
}; ?>

<div
    x-data="notificationCenter()"
    x-init="init()"
    x-on:notifications-changed.window="$wire.$refresh()"
    wire:key="notification-center-{{ Auth::user()?->uuid ?? 'guest' }}"
    class="flex flex-col gap-4"
>
    @if ($this->totalCount > 0)
        <div class="flex justify-end gap-2">
            @if ($this->unreadCount > 0)
                <x-ui.button
                    variant="ghost"
                    size="sm"
                    wire:click="markAllAsRead"
                >
                    {{ __('ui.notifications.mark_all_read') }}
                </x-ui.button>
            @endif
            <x-ui.button
                variant="error"
                size="sm"
                @click="$dispatch('confirm-modal', {
                title: '{{ addslashes(__('ui.notifications.clear_all')) }}',
                message: '{{ addslashes(__('ui.modals.confirm.message')) }}',
                confirmAction: () => $wire.clearAll()
            })"
            >
                {{ __('ui.notifications.clear_all') }} ({{ $this->totalCount }})
            </x-ui.button>
        </div>
    @endif

    <div wire:key="notifications-list">
        <div
            wire:key="notifications-content-{{ $this->notifications->count() }}"
            class="flex flex-col gap-4"
        >
            @if ($this->notifications->isEmpty())
                <x-ui.empty-state
                    icon="bell"
                    :description="__('ui.notifications.empty')"
                ></x-ui.empty-state>
            @else
                @foreach ($this->notifications as $notification)
                    <div
                        wire:key="notification-{{ $notification['id'] }}"
                        x-intersect.once="$wire.markAsRead('{{ $notification['id'] }}')"
                        class="card bg-base-200 hover:bg-base-300 transition-colors {{ $notification['isRead'] ? 'opacity-75' : '' }}"
                    >
                        <div class="card-body">
                            <div class="flex items-start gap-3">
                                <div
                                    wire:click="markAsRead('{{ $notification['id'] }}')"
                                    class="flex-shrink-0 {{ $notification['isRead'] ? 'opacity-50' : '' }} cursor-pointer"
                                >
                                    <x-ui.icon
                                        name="{{ $notification['icon']['name'] }}"
                                        class="{{ $notification['icon']['class'] }}"
                                    ></x-ui.icon>
                                </div>
                                <div
                                    wire:click="markAsRead('{{ $notification['id'] }}')"
                                    class="flex-1 cursor-pointer"
                                >
                                    <x-ui.title
                                        level="4"
                                        class="{{ $notification['isRead'] ? '' : 'font-bold' }}"
                                    >{{ $notification['title'] }}</x-ui.title>
                                    @if ($notification['subtitle'])
                                        <p class="text-sm opacity-80 mt-1">{{ $notification['subtitle'] }}</p>
                                    @endif
                                    @if ($notification['content'])
                                        <div class="text-sm mt-2">{!! $notification['content'] !!}</div>
                                    @endif
                                    @if ($notification['link'])
                                        <x-ui.link
                                            href="{{ $notification['link'] }}"
                                            class="text-sm mt-2 inline-block"
                                            underline
                                        >{{ __('ui.notifications.view') }}</x-ui.link>
                                    @endif
                                    <p class="text-xs opacity-60 mt-2">
                                        {{ $notification['createdAt']->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if (!$notification['isRead'])
                                        <x-ui.badge
                                            variant="primary"
                                            size="sm"
                                        >{{ __('ui.notifications.unread') }}</x-ui.badge>
                                    @endif
                                    <x-ui.button
                                        variant="ghost"
                                        color="error"
                                        size="sm"
                                        @click.stop="$dispatch('confirm-modal', {
                                title: '{{ addslashes(__('ui.notifications.delete')) }}',
                                message: '{{ addslashes(__('ui.modals.confirm.message')) }}',
                                confirmAction: () => $wire.delete('{{ $notification['id'] }}')
                            })"
                                    >
                                        <x-ui.icon
                                            name="trash"
                                            class="h-4 w-4"
                                        ></x-ui.icon>
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div wire:key="load-more-container">
        @if ($this->remainingCount > 0)
            <div class="flex justify-center pt-2">
                <x-ui.button
                    wire:click="loadMore"
                    variant="ghost"
                    size="sm"
                >
                    {{ __('ui.notifications.see_previous') }} ({{ $this->remainingCount }})
                </x-ui.button>
            </div>
        @endif
    </div>
</div>
