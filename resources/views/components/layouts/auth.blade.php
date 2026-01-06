<!DOCTYPE html>
<html
    lang="{{ $htmlLangAttribute }}"
    dir="{{ $htmlDirAttribute }}"
    data-theme="{{ $currentTheme }}"
>

<head>
    @include('partials.head', ['layout' => 'auth'])

    @livewireStyles
</head>

<body class="min-h-screen bg-base-100">
    <x-layouts.auth.split :title="$title ?? null">
        {{ $slot }}
    </x-layouts.auth.split>

    @livewireScripts

    <x-notifications.toast-center></x-notifications.toast-center>
    <x-ui.confirm-modal></x-ui.confirm-modal>
    @stack('endBody')
</body>

</html>
