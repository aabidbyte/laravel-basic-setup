<div class="drawer lg:drawer-open">
    <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col">
        <div class="navbar bg-base-200">
            <label for="sidebar-drawer" class=" flex-none lg:hidden btn btn-square btn-ghost drawer-button">
                <x-ui.icon name="bars-3" class="h-6 w-6" />
            </label>
            <x-layouts.app.header />
        </div>

        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
    <x-layouts.app.sidebar-menu />
</div>
