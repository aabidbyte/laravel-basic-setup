@props([
    'layout' => 'app',
])

@livewireScripts(['nonce' => cspNonce()])

<x-notifications.toast-center></x-notifications.toast-center>

<x-ui.confirm-modal></x-ui.confirm-modal>

@if ($layout === 'app')
    <livewire:datatable.action-modal></livewire:datatable.action-modal>
@endif

@stack('endBody')
