@props([
    'value' => null,
    'badgeColor' => 'neutral',
    'badgeSize' => 'sm',
])

@if ($value !== null)
    <x-ui.badge :color="$badgeColor" :size="$badgeSize">
        {{ $value }}
    </x-ui.badge>
@endif

