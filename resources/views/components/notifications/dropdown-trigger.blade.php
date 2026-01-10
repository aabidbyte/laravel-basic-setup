{{--
    Notification Dropdown Trigger - Static Blade Wrapper

    This is a static Blade component that provides the trigger button with bell icon
    and unread badge. The actual notification list is lazy-loaded via a nested
    Livewire component to prevent UI flicker during navigation.

    Props:
    - None (calculates initial unread count from auth user)
--}}
@php
    use Illuminate\Support\Facades\Auth;

    // Calculate initial unread count on server
    $user = Auth::user();
    $initialUnreadCount = $user ? $user->unreadNotifications()->count() : 0;
@endphp

<div x-data="notificationDropdownTrigger(@js($initialUnreadCount))"
     @click.away="close()"
     class="relative">
    <x-ui.dropdown placement="end"
                   menu
                   menuSize="sm"
                   contentClass="min-w-80 max-h-96 overflow-y-auto"
                   x-bind:class="{ 'dropdown-open': isOpen }">
        <x-slot:trigger>
            <button x-ref="trigger"
                    class="btn btn-ghost btn-circle relative"
                    type="button"
                    @click="toggle()">
                <x-ui.icon name="bell"
                           class="h-5 w-5"></x-ui.icon>
                {{-- Badge managed by Alpine, visible when count > 0 --}}
                <template x-if="unreadCount > 0">
                    <x-ui.badge color="error"
                                size="xs"
                                class="absolute -right-1 -top-1 h-4 w-4 justify-center"
                                x-bind:aria-label="'{{ __('notifications.unread') }}: ' + unreadCount"
                                x-text="unreadCount > 99 ? '99+' : unreadCount"></x-ui.badge>
                </template>
            </button>
        </x-slot:trigger>

        {{-- Lazy-loaded content component --}}
        <livewire:notifications.dropdown-content lazy
                                                 wire:key="notifications-dropdown-content"></livewire:notifications.dropdown-content>
    </x-ui.dropdown>
</div>
