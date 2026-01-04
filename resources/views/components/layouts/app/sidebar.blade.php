<div class="drawer lg:drawer-open">
    <input
        id="sidebar-drawer"
        type="checkbox"
        class="drawer-toggle"
    />
    <div class="drawer-content flex flex-col">
        <div class="navbar bg-base-200">
            <label
                for="sidebar-drawer"
                class=" flex-none lg:hidden btn btn-square btn-ghost drawer-button"
            >
                <x-ui.icon
                    name="bars-3"
                    class="h-6 w-6"
                ></x-ui.icon>
            </label>
            <x-layouts.app.header></x-layouts.app.header>
        </div>

        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
    <div class="drawer-side">
        <label
            for="sidebar-drawer"
            aria-label="close sidebar"
            class="drawer-overlay"
        ></label>
        <aside class="flex flex-col h-full w-64 bg-base-200 overflow-y-auto">
            <div class="p-4">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-2"
                    wire:navigate
                >
                    <x-app-logo></x-app-logo>
                </a>
            </div>
            <x-layouts.app.sidebar-menus></x-layouts.app.sidebar-menus>
        </aside>
    </div>
</div>
