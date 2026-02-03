@php
    $user = Auth::user();
@endphp

<x-ui.dropdown placement="end"
               menu
               menuSize="sm"
               contentClass="sidebar-user-menus"
               title="{{ $user->name }}">
    <x-slot:trigger>
        <div class="btn btn-ghost btn-circle">
            <x-ui.avatar :imageSrc="$user->avatar_url ?? null"
                         :initials="$user->initials()"
                         size="sm"></x-ui.avatar>
        </div>
    </x-slot:trigger>

    {{-- User menu items --}}
    <div class="menu-items">
        @foreach ($sideBarUserMenus as $group)
            @foreach ($group['items'] ?? [] as $item)
                <x-navigation.item :item="$item"></x-navigation.item>
            @endforeach
        @endforeach
    </div>

    <x-slot:actions>
        <div class="mx-auto">
            <form method="POST"
                  action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit"
                             class="w-full">
                    {{ __('actions.logout') }}
                </x-ui.button>
            </form>
        </div>
    </x-slot:actions>
</x-ui.dropdown>
