<div class="flex-1">
    <h1 class="text-xl font-semibold">{{ $pageTitle ?? config('app.name') }}</h1>
    @if (isset($pageSubtitle) && $pageSubtitle)
        <p class="text-sm text-base-content/70 mt-1">{{ $pageSubtitle }}</p>
    @endif
</div>
<div class="flex-none flex items-center gap-2">
    <livewire:notifications.dropdown lazy>
        <x-slot:placeholder>
            <button class="btn btn-ghost btn-circle" type="button" disabled>
                <x-ui.icon name="bell" class="h-5 w-5" />
            </button>
        </x-slot:placeholder>
    </livewire:notifications.dropdown>
    <x-preferences.theme-switcher />
    <x-preferences.locale-switcher />
    <x-ui.dropdown placement="end" menu menuSize="sm" contentClass="sidebar-user-menus">
        <x-slot:trigger>
            <div class="btn btn-ghost btn-circle avatar">
                <div class="w-10 rounded-full bg-base-300 text-base-content">
                    <span class="text-xs">{{ Auth::user()->initials() }}</span>
                </div>
            </div>
        </x-slot:trigger>

        <div class="menu-title">
            <span>{{ Auth::user()->name }}</span>
        </div>
        @foreach ($sideBarUserMenus as $group)
            @foreach ($group['items'] ?? [] as $item)
                <x-navigation.item :item="$item" />
            @endforeach
        @endforeach
        <form method="POST" action="{{ route('logout') }}" class="mx-auto">
            @csrf
            <x-ui.button type="submit" class="w-full">
                {{ __('ui.actions.logout') }}
            </x-ui.button>
        </form>
    </x-ui.dropdown>
</div>
