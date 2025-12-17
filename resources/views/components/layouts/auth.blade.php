<!DOCTYPE html>
<html lang="{{ $htmlLangAttribute }}" dir="{{ $htmlDirAttribute }}" data-theme="{{ $currentTheme }}">

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body class="min-h-screen bg-base-100">
    <x-layouts.auth.split :title="$title ?? null">
        {{ $slot }}
    </x-layouts.auth.split>

    @livewireScripts
</body>

</html>
