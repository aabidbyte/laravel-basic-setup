@props(['topMenus', 'bottomMenus', 'userMenus'])

<div class="drawer-side sidebar-desktop">
    <label for="sidebar-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <aside class="flex flex-col h-full w-64 bg-base-200 overflow-y-auto">
        <div class="p-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2" wire:navigate>
                <x-app-logo />
            </a>
        </div>
        <div class="flex flex-col flex-1 h-full ">

            {{-- Top menus (Platform section) --}}
            <div class="menu sidebar-top-menus">
                @foreach ($topMenus as $group)
                    <x-navigation.group :group="$group" />
                @endforeach
            </div>

            {{-- Bottom menus (Resources section) --}}
            <div class="menu sidebar-bottom-menus">
                @foreach ($bottomMenus as $group)
                    <x-navigation.group :group="$group" />
                @endforeach
            </div>
        </div>
        <div class="p-4 border-t border-base-300">
            <div class="mb-2 flex justify-center gap-2">
                <x-preferences.theme-switcher />
                <x-preferences.locale-switcher />
            </div>
            <div class="dropdown dropdown-top">
                <div tabindex="0" role="button" class="btn btn-ghost w-full justify-start"
                    data-test="sidebar-menu-button">
                    <div class="avatar placeholder">
                        <div class="w-10 rounded-full bg-base-300 text-base-content">
                            <span class="text-xs">{{ auth()->user()->initials() }}</span>
                        </div>
                    </div>
                    <div class="flex-1 text-left">
                        <div class="font-bold">{{ auth()->user()->name }}</div>
                        <div class="text-xs opacity-50">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div tabindex="0" class="menu menu-sm dropdown-content sidebar-user-menus">
                    @foreach ($userMenus as $group)
                        <x-navigation.group :group="$group" />
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}" class="mx-auto">
                        @csrf
                        <x-ui.button type="submit" class="w-full">
                            {{ __('ui.actions.logout') }}
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div>
