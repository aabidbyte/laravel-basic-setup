<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public function refreshNotifications(): void
    {
        $this->dispatch('$refresh');
    }

    public function getNotificationsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }

        // Get all notifications and sort: unread first, then by created_at desc
        return $user
            ->notifications()
            ->latest()
            ->get()
            ->sortBy([
                ['read_at', 'asc'], // null (unread) comes before dates (read)
                ['created_at', 'desc'],
            ])
            ->take(5)
            ->values();
    }

    public function getFormattedNotificationsProperty(): array
    {
        return $this->notifications
            ->map(function ($notification) {
                $data = $notification->data;
                $type = $data['type'] ?? 'classic';
                $link = $data['link'] ?? null;
                $isRead = $notification->read_at !== null;

                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'subtitle' => $data['subtitle'] ?? null,
                    'type' => $type,
                    'link' => $link,
                    'isRead' => $isRead,
                    'createdAt' => $notification->created_at,
                    'iconName' => $this->getIconNameForType($type),
                    'iconClass' => $this->getIconClassForType($type),
                    'linkClass' => $this->getLinkClass($isRead),
                    'hasLink' => !empty($link),
                    'wrapperClass' => $this->getLinkClass($isRead) . (empty($link) ? ' cursor-default' : ''),
                    'markAsReadAction' => !$isRead ? "markAsRead('{$notification->id}')" : null,
                ];
            })
            ->toArray();
    }

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

    protected function getLinkClass(bool $isRead): string
    {
        return $isRead ? '' : 'font-semibold';
    }

    public function getUnreadCountProperty(): int
    {
        $user = Auth::user();
        if (!$user) {
            return 0;
        }

        return $user->unreadNotifications->count();
    }

    public function getUnreadBadgeProperty(): string
    {
        $count = $this->unreadCount;

        return $count > 99 ? '99+' : (string) $count;
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function markVisibleAsRead(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $this->notifications->each(function ($notification) {
            if ($notification->read_at) {
                return;
            }

            $notification->markAsRead();
        });
    }
}; ?>

<div x-data="notificationDropdown($wire)" x-init="init()" x-on:notifications-changed.window="$wire.$refresh();"
    @click.away="
        if (wasOpened) {
            $wire.markVisibleAsRead();
            wasOpened = false;
        }
        isOpen = false;
    "
    wire:key="notification-dropdown-{{ Auth::id() ?? 'guest' }}">
    <x-ui.dropdown placement="end" menu menuSize="sm" contentClass="w-80 max-h-96 overflow-y-auto"
        x-bind:class="{ 'dropdown-open': isOpen }">
        <x-slot:trigger>
            <button x-ref="trigger" class="btn btn-ghost btn-circle relative" type="button"
                @click="
                    isOpen = true;
                    wasOpened = true;
                ">
                <x-ui.icon name="bell" class="h-5 w-5" />
                @if ($this->unreadCount > 0)
                    <span class="badge badge-error badge-xs absolute -top-1 -right-1 w-4 h-4 justify-center "
                        aria-label="{{ __('ui.notifications.unread') }}: {{ $this->unreadCount }}">
                        {{ $this->unreadBadge }}
                    </span>
                @endif
            </button>
        </x-slot:trigger>

        <div class="menu-title">
            <span>{{ __('ui.notifications.dropdown.title') }}</span>
        </div>

        @forelse($this->formattedNotifications as $notification)
            <div>
                @if ($notification['hasLink'])
                    <a href="{{ $notification['link'] }}" class="{{ $notification['wrapperClass'] }}" wire:navigate
                        @if ($notification['markAsReadAction']) wire:click="{{ $notification['markAsReadAction'] }}" @endif>
                        <x-notifications.notification-item :iconName="$notification['iconName']" :iconClass="$notification['iconClass']" :title="$notification['title']"
                            :subtitle="$notification['subtitle']" :createdAt="$notification['createdAt']" :isRead="$notification['isRead']" />
                    </a>
                @else
                    <div class="{{ $notification['wrapperClass'] }}"
                        @if ($notification['markAsReadAction']) wire:click="{{ $notification['markAsReadAction'] }}" @endif>
                        <x-notifications.notification-item :iconName="$notification['iconName']" :iconClass="$notification['iconClass']" :title="$notification['title']"
                            :subtitle="$notification['subtitle']" :createdAt="$notification['createdAt']" :isRead="$notification['isRead']" />
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-4 text-sm opacity-60">
                {{ __('ui.notifications.empty') }}
            </div>
        @endforelse

        <div class="divider my-1"></div>

        <x-ui.button href="{{ route('notifications.index') }}" wire:navigate class="text-center">
            {{ __('ui.notifications.view_all') }}
        </x-ui.button>
    </x-ui.dropdown>
</div>
