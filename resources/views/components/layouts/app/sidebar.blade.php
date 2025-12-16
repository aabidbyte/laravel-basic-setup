@inject('menuService', \App\Services\SideBarMenuService::class)

<div class="drawer lg:drawer-open">
    <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col">
        <x-layouts.app.mobile-menu :topMenus="$menuService->getTopMenus()" :bottomMenus="$menuService->getBottomMenus()" :userMenus="$menuService->getUserMenus()" />

        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
    <x-layouts.app.desktop-menu :topMenus="$menuService->getTopMenus()" :bottomMenus="$menuService->getBottomMenus()" :userMenus="$menuService->getUserMenus()" />
</div>
