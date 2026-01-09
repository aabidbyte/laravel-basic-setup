<div class="flex-1">
    <h1 class="text-xl font-semibold">{{ $pageTitle ?? config('app.name') }}</h1>
    @if (isset($pageSubtitle) && $pageSubtitle)
        <p class="text-base-content/70 mt-1 text-sm">{{ $pageSubtitle }}</p>
    @endif
</div>
{{-- Header Actions --}}
<div class="flex items-center gap-2">
    <x-notifications.dropdown-trigger></x-notifications.dropdown-trigger>

    <x-layouts.app.user-menus></x-layouts.app.user-menus>
</div>
