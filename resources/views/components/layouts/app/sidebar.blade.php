<div class="drawer lg:drawer-open"
     x-data="{ drawerOpen: false }">
    <input id="sidebar-drawer"
           type="checkbox"
           class="drawer-toggle"
           x-model="drawerOpen" />
    <div class="drawer-content flex flex-col">
        <div class="navbar bg-base-200">
            <label for="sidebar-drawer"
                   class="btn btn-square btn-ghost drawer-button flex-none lg:hidden">
                <x-ui.icon name="bars-3"
                           class="h-6 w-6"></x-ui.icon>
            </label>
            <x-layouts.app.header></x-layouts.app.header>
        </div>

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>
    <div class="drawer-side z-40">
        <label for="sidebar-drawer"
               aria-label="close sidebar"
               class="drawer-overlay"></label>
        <aside class="bg-base-200 flex h-full w-64 flex-col overflow-y-auto">
            <div class="p-4">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2"
                   wire:navigate>
                    <x-app-logo></x-app-logo>
                </a>
            </div>
            <x-layouts.app.sidebar-menus></x-layouts.app.sidebar-menus>
        </aside>
    </div>
</div>
