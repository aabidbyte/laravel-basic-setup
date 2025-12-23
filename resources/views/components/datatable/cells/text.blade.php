@props([
    'value' => null,
    'bold' => false,
    'muted' => false,
    'class' => '',
])

<div class="{{ $class }}">
    @if ($bold)
        <div class="font-medium">{{ $value ?? '' }}</div>
    @else
        <div class="text-sm {{ $muted ? 'text-base-content/70' : '' }}">
            {{ $value ?? '' }}
        </div>
    @endif
</div>

