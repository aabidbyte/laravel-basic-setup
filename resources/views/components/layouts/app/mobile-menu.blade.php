@props(['topMenus', 'bottomMenus', 'userMenus'])

<div class="navbar bg-base-200 lg:hidden sidebar-mobile">
    <div class="flex-none">
        <label for="sidebar-drawer" class="btn btn-square btn-ghost drawer-button">
            <x-ui.icon name="bars-3" class="h-6 w-6" />
        </label>
    </div>
    <div class="flex-1">
        <a href="{{ route('dashboard') }}" class="btn btn-ghost text-xl" wire:navigate>
            <x-app-logo />
        </a>
    </div>
    <div class="flex-none">
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                <div class="w-10 rounded-full bg-base-300 text-base-content">
                    <span class="text-xs">{{ auth()->user()->initials() }}</span>
                </div>
            </div>
            <div tabindex="0" class="menu menu-sm dropdown-content sidebar-user-menus">
                <div class="menu-title">
                    <span>{{ auth()->user()->name }}</span>
                </div>
                @foreach ($userMenus as $group)
                    @foreach ($group['items'] ?? [] as $item)
                        <x-navigation.item :item="$item" />
                    @endforeach
                @endforeach
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full" data-test="logout-button">
                        {{ __('ui.actions.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
