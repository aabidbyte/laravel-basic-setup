<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public function getNotificationsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        // Get all notifications and sort: unread first, then by created_at desc
        return Auth::user()
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

                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'subtitle' => $data['subtitle'] ?? null,
                    'type' => $type,
                    'link' => $link ?? route('notifications.index'),
                    'isRead' => $notification->read_at !== null,
                    'createdAt' => $notification->created_at,
                    'iconName' => $this->getIconNameForType($type),
                    'iconClass' => $this->getIconClassForType($type),
                    'linkClass' => $this->getLinkClass($notification->read_at !== null),
                    'hasWireNavigate' => $link !== null,
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
        return Auth::user()->unreadNotifications->count();
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }
}; ?>

<x-ui.dropdown placement="end" menu menuSize="sm" contentClass="w-80 max-h-96 overflow-y-auto">
    <x-slot:trigger>
        <button class="btn btn-ghost btn-circle relative" type="button">
            <x-ui.icon name="bell" class="h-5 w-5" />
            @if ($this->unreadCount > 0)
                <span class="status status-error absolute top-0 right-0"></span>
            @endif
        </button>
    </x-slot:trigger>

    <div class="menu-title">
        <span>{{ __('ui.notifications.dropdown.title') }}</span>
    </div>

    @forelse($this->formattedNotifications as $notification)
        <div>
            <a href="{{ $notification['link'] }}" class="{{ $notification['linkClass'] }}"
                @if ($notification['hasWireNavigate']) wire:navigate @endif
                @if (!$notification['isRead']) wire:click="markAsRead('{{ $notification['id'] }}')" @endif>
                <div class="flex items-start gap-2">
                    <div class="flex-shrink-0 mt-0.5">
                        <x-ui.icon name="{{ $notification['iconName'] }}" class="{{ $notification['iconClass'] }}" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="truncate">{{ $notification['title'] }}</div>
                        @if ($notification['subtitle'])
                            <div class="text-xs opacity-70 truncate">{{ $notification['subtitle'] }}</div>
                        @endif
                        <div class="text-xs opacity-60 mt-1">
                            {{ $notification['createdAt']->diffForHumans() }}
                        </div>
                    </div>
                    @if (!$notification['isRead'])
                        <div class="badge badge-primary badge-xs"></div>
                    @endif
                </div>
            </a>
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
