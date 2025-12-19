<x-ui.dropdown placement="end" menu menuSize="sm" contentClass="sidebar-user-menus">
    <x-slot:trigger>
        <div class="btn btn-ghost btn-circle avatar">
            <div class="w-10 rounded-full bg-base-300 text-base-content flex items-center justify-center">
                <span class="text-xs uppercase">{{ Auth::user()->initials() }}</span>
            </div>
        </div>
    </x-slot:trigger>

    <div class="menu-title text-center">
        <span>{{ Auth::user()->name }}</span>
    </div>

    {{-- Mobile/Tablet: Show notifications, theme, and locale in user menu --}}
    <div class="lg:hidden flex flex-row items-center gap-2 self-center">
        <div>
            <x-preferences.theme-switcher />
        </div>
        <div>
            <x-preferences.locale-switcher />
        </div>
    </div>

    {{-- User menu items --}}
    <div class="menu-items">
        @foreach ($sideBarUserMenus as $group)
            @foreach ($group['items'] ?? [] as $item)
                <x-navigation.item :item="$item" />
            @endforeach
        @endforeach
    </div>

    <div class="divider my-1"></div>
    <div class="mx-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" class="w-full">
                {{ __('ui.actions.logout') }}
            </x-ui.button>
        </form>
    </div>
</x-ui.dropdown>
