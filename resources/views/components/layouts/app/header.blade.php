<div class="flex-1">
    <h1 class="text-xl font-semibold">{{ $pageTitle ?? config('app.name') }}</h1>
    @if (isset($pageSubtitle) && $pageSubtitle)
        <p class="text-sm text-base-content/70 mt-1">{{ $pageSubtitle }}</p>
    @endif
</div>
<div class="flex-none flex items-center gap-2">
    <x-preferences.theme-switcher />
    <x-preferences.locale-switcher />
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
            <div class="w-10 rounded-full bg-base-300 text-base-content">
                <span class="text-xs">{{ auth()->user()->initials() }}</span>
            </div>
        </div>
        <div tabindex="0" class="menu menu-sm dropdown-content sidebar-user-menus">
            <div class="menu-title">
                <span>{{ auth()->user()->name }}</span>
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
        </div>
    </div>
</div>
