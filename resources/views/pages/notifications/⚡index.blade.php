<?php

use App\Livewire\BasePageComponent;
use Illuminate\Support\Facades\Auth;

new class extends BasePageComponent
{
    public ?string $pageTitle = 'ui.pages.notifications';

    public function getNotificationsProperty(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Auth::user()->notifications()->latest()->paginate(20);
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function getUnreadCountProperty(): int
    {
        return Auth::user()->unreadNotifications->count();
    }
}; ?>

<div class="flex flex-col gap-4">
    @if ($this->getUnreadCountProperty() > 0)
        <div class="flex justify-end">
            <button wire:click="markAllAsRead" class="btn btn-sm btn-ghost">
                {{ __('ui.notifications.mark_all_read') }}
            </button>
        </div>
    @endif

    @forelse($this->notifications as $notification)
        @php
            $data = $notification->data;
            $title = $data['title'] ?? 'Notification';
            $subtitle = $data['subtitle'] ?? null;
            $content = $data['content'] ?? null;
            $type = $data['type'] ?? 'classic';
            $link = $data['link'] ?? null;
            $isRead = $notification->read_at !== null;
        @endphp

        <div x-intersect.once="$wire.markAsRead('{{ $notification->id }}')"
            wire:click="markAsRead('{{ $notification->id }}')"
            class="card bg-base-200 cursor-pointer hover:bg-base-300 transition-colors {{ $isRead ? 'opacity-75' : '' }}"
            role="button" tabindex="0">
            <div class="card-body">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 {{ $isRead ? 'opacity-50' : '' }}">
                        @if ($type === 'success')
                            <x-ui.icon name="check-circle" class="h-6 w-6 text-success" />
                        @elseif($type === 'info')
                            <x-ui.icon name="information-circle" class="h-6 w-6 text-info" />
                        @elseif($type === 'warning')
                            <x-ui.icon name="exclamation-triangle" class="h-6 w-6 text-warning" />
                        @elseif($type === 'error')
                            <x-ui.icon name="x-circle" class="h-6 w-6 text-error" />
                        @else
                            <x-ui.icon name="bell" class="h-6 w-6 text-base-content" />
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg {{ $isRead ? '' : 'font-bold' }}">
                            {{ $title }}
                        </h3>
                        @if ($subtitle)
                            <p class="text-sm opacity-80 mt-1">{{ $subtitle }}</p>
                        @endif
                        @if ($content)
                            <div class="text-sm mt-2">{!! $content !!}</div>
                        @endif
                        @if ($link)
                            <a href="{{ $link }}" class="text-sm text-primary underline mt-2 inline-block">
                                {{ __('ui.notifications.view') }}
                            </a>
                        @endif
                        <p class="text-xs opacity-60 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if (!$isRead)
                        <div class="badge badge-primary badge-sm">{{ __('ui.notifications.unread') }}</div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card bg-base-200">
            <div class="card-body text-center">
                <x-ui.icon name="bell" class="h-12 w-12 mx-auto opacity-50 mb-4" />
                <p class="text-base-content opacity-60">{{ __('ui.notifications.empty') }}</p>
            </div>
        </div>
    @endforelse

    @if ($this->notifications->hasPages())
        <div class="mt-4">
            {{ $this->notifications->links() }}
        </div>
    @endif
</div>
