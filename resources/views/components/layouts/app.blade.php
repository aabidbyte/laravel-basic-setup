<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body>
    <x-layouts.app.sidebar :title="$title ?? null">
        <main class="flex-1">
            {{ $slot }}
        </main>
    </x-layouts.app.sidebar>

    @livewireScripts
</body>

</html>
