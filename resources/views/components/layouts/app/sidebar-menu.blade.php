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
                @foreach ($sideBarTopMenus as $group)
                    <x-navigation.group :group="$group" />
                @endforeach
            </div>

            {{-- Bottom menus (Resources section) --}}
            <div class="menu sidebar-bottom-menus">
                @foreach ($sideBarBottomMenus as $group)
                    <x-navigation.group :group="$group" />
                @endforeach
            </div>
        </div>
    </aside>
</div>
