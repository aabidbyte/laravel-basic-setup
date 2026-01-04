<!DOCTYPE html>
<html
    lang="{{ $htmlLangAttribute }}"
    dir="{{ $htmlDirAttribute }}"
    data-theme="{{ $currentTheme }}"
>

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body>
    <x-layouts.app.sidebar>
        {{ $slot }}
    </x-layouts.app.sidebar>

    @livewireScripts

    <x-notifications.toast-center></x-notifications.toast-center>
    <x-ui.confirm-modal></x-ui.confirm-modal>
    <livewire:datatable.action-modal></livewire:datatable.action-modal>
    @stack('endBody')
</body>

</html>
