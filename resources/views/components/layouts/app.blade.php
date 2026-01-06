<!DOCTYPE html>
<html
    lang="{{ $htmlLangAttribute }}"
    dir="{{ $htmlDirAttribute }}"
    data-theme="{{ $currentTheme }}"
>

<head>
    @include('partials.head', ['layout' => 'app'])

</head>

<body>
    <x-layouts.app.sidebar>
        {{ $slot }}
    </x-layouts.app.sidebar>
    
    
    <x-notifications.toast-center></x-notifications.toast-center>

    <x-ui.confirm-modal></x-ui.confirm-modal>
    
    <livewire:datatable.action-modal></livewire:datatable.action-modal>
    
    @livewireScripts(['nonce' => cspNonce()])

    @stack('endBody')
</body>

</html>
