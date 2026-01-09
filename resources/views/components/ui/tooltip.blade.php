@props([
    'text' => null,
    'position' => 'top', // top, bottom, left, right
    'open' => false, // Whether tooltip is open (for controlled state)
])

@php
    $tooltipId = $attributes->get('id') ?? uniqid('tooltip-');
    $positionClasses = [
        'top' => 'tooltip-top',
        'bottom' => 'tooltip-bottom',
        'left' => 'tooltip-left',
        'right' => 'tooltip-right',
    ];
    $positionClass = $positionClasses[$position] ?? 'tooltip-top';
@endphp

<div class="tooltip {{ $positionClass }}"
     data-tip="{{ $text }}"
     {{ $attributes->except(['text', 'position', 'open']) }}>
    {{ $slot }}
</div>
