{{-- Sidebar Menus: Top menus (Platform section) and bottom menus (Resources section) --}}
<div class="flex h-full flex-1 flex-col">
    <div class="menu sidebar-top-menus">
        @foreach ($sideBarTopMenus as $group)
            <x-navigation.group :group="$group"></x-navigation.group>
        @endforeach
    </div>

    <div class="menu sidebar-bottom-menus">
        @foreach ($sideBarBottomMenus as $group)
            <x-navigation.group :group="$group"></x-navigation.group>
        @endforeach
    </div>
</div>
