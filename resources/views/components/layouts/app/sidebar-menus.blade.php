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
