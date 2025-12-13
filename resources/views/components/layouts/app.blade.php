<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body>
    <x-layouts.app.sidebar :title="$title ?? null">
        {{ $slot }}
    </x-layouts.app.sidebar>

    @livewireScripts
</body>

</html>
