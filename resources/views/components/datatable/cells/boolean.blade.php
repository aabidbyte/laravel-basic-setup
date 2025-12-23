@props([
    'value' => null,
    'trueLabel' => null,
    'falseLabel' => null,
])

@if ($value)
    <x-ui.badge color="success" size="sm">
        {{ $trueLabel ?? __('ui.table.yes') }}
    </x-ui.badge>
@else
    <x-ui.badge color="warning" size="sm">
        {{ $falseLabel ?? __('ui.table.no') }}
    </x-ui.badge>
@endif

