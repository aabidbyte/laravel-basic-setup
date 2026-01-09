<x-ui.dropdown placement="end"
               menu
               menuSize="sm"
               contentClass="sidebar-user-menus">
    <x-slot:trigger>
        <div class="btn btn-ghost btn-circle">
            <x-ui.avatar :user="Auth::user()"
                         size="sm"></x-ui.avatar>
        </div>
    </x-slot:trigger>

    <div class="menu-title text-center">
        <span>{{ Auth::user()->name }}</span>
    </div>

    {{-- User menu items --}}
    <div class="menu-items">
        @foreach ($sideBarUserMenus as $group)
            @foreach ($group['items'] ?? [] as $item)
                <x-navigation.item :item="$item"></x-navigation.item>
            @endforeach
        @endforeach
    </div>

    <div class="divider my-1"></div>
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
</x-ui.dropdown>
