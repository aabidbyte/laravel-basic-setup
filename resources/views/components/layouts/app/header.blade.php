<div class="flex-1">
    <h1 class="text-xl font-semibold">{{ $pageTitle ?? config('app.name') }}</h1>
    @if (isset($pageSubtitle) && $pageSubtitle)
        <p class="text-sm text-base-content/70 mt-1">{{ $pageSubtitle }}</p>
    @endif
</div>
<div class="flex-none flex items-center gap-2">
    {{-- Desktop: Show notifications, theme, and locale in header --}}
    <livewire:notifications.dropdown lazy wire:key="notifications-dropdown-desktop" />
    <div class="hidden lg:flex items-center gap-2">
        <x-preferences.theme-switcher />
        <x-preferences.locale-switcher />
    </div>

    {{-- User menu dropdown --}}
    <x-layouts.app.user-menus />
</div>
