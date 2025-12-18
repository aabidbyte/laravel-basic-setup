<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

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

@if (Auth::check())
    <x-ui.dropdown placement="end" menu menuSize="sm" contentClass="w-80 max-h-96 overflow-y-auto">
        <x-slot:trigger>
            <button class="btn btn-ghost btn-circle relative" type="button">
                <x-ui.icon name="bell" class="h-5 w-5" />
                @if ($this->unreadCount > 0)
                    <span class="status status-error absolute top-0 right-0">
                        <span
                            class="absolute inline-flex h-full w-full rounded-full bg-error opacity-75 animate-ping"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-error"></span>
                    </span>
                @endif
            </button>
        </x-slot:trigger>

        <div class="menu-title">
            <span>{{ __('ui.notifications.dropdown.title') }}</span>
        </div>

        @forelse($this->notifications as $notification)
            @php
                $data = $notification->data;
                $title = $data['title'] ?? 'Notification';
                $subtitle = $data['subtitle'] ?? null;
                $type = $data['type'] ?? 'classic';
                $link = $data['link'] ?? null;
                $isRead = $notification->read_at !== null;
            @endphp

            <li>
                <a href="{{ $link ?? route('notifications.index') }}"
                    class="{{ $isRead ? '' : 'font-semibold' }} {{ $link ? 'wire:navigate' : '' }}"
                    @if (!$isRead) wire:click="markAsRead('{{ $notification->id }}')" @endif>
                    <div class="flex items-start gap-2">
                        <div class="flex-shrink-0 mt-0.5">
                            @if ($type === 'success')
                                <x-ui.icon name="check-circle" class="h-4 w-4 text-success" />
                            @elseif($type === 'info')
                                <x-ui.icon name="information-circle" class="h-4 w-4 text-info" />
                            @elseif($type === 'warning')
                                <x-ui.icon name="exclamation-triangle" class="h-4 w-4 text-warning" />
                            @elseif($type === 'error')
                                <x-ui.icon name="x-circle" class="h-4 w-4 text-error" />
                            @else
                                <x-ui.icon name="bell" class="h-4 w-4 text-base-content" />
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="truncate">{{ $title }}</div>
                            @if ($subtitle)
                                <div class="text-xs opacity-70 truncate">{{ $subtitle }}</div>
                            @endif
                            <div class="text-xs opacity-60 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        @if (!$isRead)
                            <div class="badge badge-primary badge-xs"></div>
                        @endif
                    </div>
                </a>
            </li>
        @empty
            <li>
                <div class="text-center py-4 text-sm opacity-60">
                    {{ __('ui.notifications.empty') }}
                </div>
            </li>
        @endforelse

        <div class="divider my-1"></div>

        <li>
            <a href="{{ route('notifications.index') }}" wire:navigate class="text-center">
                {{ __('ui.notifications.view_all') }}
            </a>
        </li>
    </x-ui.dropdown>
@endif
