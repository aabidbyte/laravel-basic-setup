{{--
    Badge Component Props:
    - variant: 'solid', 'outline', 'ghost', 'soft', 'dash' (Default: 'solid')
    - color: 'neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'
    - size: 'xs', 'sm', 'md', 'lg', 'xl'
    - class: Additional classes
    - text: Text content (alternative to slot for programmatic rendering)
--}}
@props([
    'variant' => 'solid',
    'color' => null,
    'size' => 'md',
    'class' => '',
    'text' => null,
])

@php
    $variantClasses = [
        'solid' => '',
        'outline' => 'badge-outline',
        'ghost' => 'badge-ghost',
        'soft' => 'badge-soft',
        'dash' => 'badge-dash',
    ];

    $colorClasses = [
        'neutral' => 'badge-neutral',
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'accent' => 'badge-accent',
        'info' => 'badge-info',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
    ];

    $sizeClasses = [
        'xs' => 'badge-xs',
        'sm' => 'badge-sm',
        'md' => '',
        'lg' => 'badge-lg',
        'xl' => 'badge-xl',
    ];

    $variantClass = $variantClasses[$variant] ?? '';
    $colorClass = isset($color) ? $colorClasses[$color] ?? '' : '';
    $sizeClass = $sizeClasses[$size] ?? '';

    $classes = trim("badge {$variantClass} {$colorClass} {$sizeClass} {$class}");
    $finalClass = $classes . ' whitespace-nowrap';
@endphp

<span class="{!! $finalClass !!}"
      {{ $attributes->except(['variant', 'color', 'size', 'class', 'text']) }}>
    {{ $text ?? $slot }}
</span>
