<div class="flex-1">
    <h1 class="text-xl font-semibold">{{ $pageTitle ?? config('app.name') }}</h1>
    @if (isset($pageSubtitle) && $pageSubtitle)
        <p class="text-sm text-base-content/70 mt-1">{{ $pageSubtitle }}</p>
    @endif
</div>
{{-- Header Actions: Desktop shows notifications, theme, and locale; mobile shows in user menu --}}
<div class="flex-none flex items-center gap-2">
    <livewire:notifications.dropdown lazy wire:key="notifications-dropdown-desktop"></livewire:notifications.dropdown>
    <div class="hidden lg:flex items-center gap-2">
        <x-preferences.theme-switcher></x-preferences.theme-switcher>
        <x-preferences.locale-switcher></x-preferences.locale-switcher>
    </div>

    <x-layouts.app.user-menus></x-layouts.app.user-menus>
</div>
