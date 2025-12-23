@props([
    'value' => null,
    'href' => '#',
    'external' => false,
    'label' => null,
])

@if ($external)
    <a href="{{ $href }}" class="link link-primary" target="_blank" rel="noopener noreferrer">
        {{ $value ?? ($label ?? '') }}
    </a>
@else
    <a href="{{ $href }}" class="link link-primary">
        {{ $value ?? ($label ?? '') }}
    </a>
@endif

