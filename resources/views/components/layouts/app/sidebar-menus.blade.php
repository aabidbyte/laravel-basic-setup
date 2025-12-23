{{-- Sidebar Menus: Top menus (Platform section) and bottom menus (Resources section) --}}
<div class="flex flex-col flex-1 h-full ">
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
