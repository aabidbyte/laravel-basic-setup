{{--
    Button Component Props:
    - variant: 'solid', 'outline', 'ghost', 'link', 'soft', 'dash' (Default: 'solid')
    - color: 'primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'
    - size: 'xs', 'sm', 'md', 'lg', 'xl'
    - type: HTML button type attribute
    - circle: boolean - makes the button circular
--}}
@props([
    'variant' => 'solid',
    'color' => null,
    'size' => 'md',
    'type' => null,
    'text' => null,
    'href' => null,
    'circle' => false,
])

@php
    $variantClasses = [
        'solid' => '',
        'outline' => 'btn-outline',
        'ghost' => 'btn-ghost',
        'link' => 'btn-link',
        'soft' => 'btn-soft',
        'dash' => 'btn-dash',
    ];

    $colorClasses = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent' => 'btn-accent',
        'neutral' => 'btn-neutral',
        'info' => 'btn-info',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
        'error' => 'btn-error',
    ];

    $sizeClasses = [
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
        'xl' => 'btn-xl',
    ];

    $variantClass = $variantClasses[$variant] ?? '';
    $colorClass = isset($color) ? ($colorClasses[$color] ?? '') : '';
    $sizeClass = $sizeClasses[$size] ?? '';
    $circleClass = $circle ? 'btn-circle' : '';
    
    $btnClasses = trim(implode(' ', [$variantClass, $colorClass, $sizeClass, $circleClass]));
@endphp

@if ($href ?? false)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => trim("btn {$btnClasses}")])->except(['variant', 'color', 'size', 'type', 'text', 'href', 'circle']) }}>
        {{ $text ?? $slot }}
    </a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => trim("btn {$btnClasses}")])->except(['variant', 'color', 'size', 'type', 'text', 'circle']) }}>
        {{ $text ?? $slot }}
    </button>
@endif
