<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body>
    <x-layouts.auth.simple :title="$title ?? null">
        {{ $slot }}
    </x-layouts.auth.simple>

    @livewireScripts
</body>

</html>
