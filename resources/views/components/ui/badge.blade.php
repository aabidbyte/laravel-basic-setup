{{--
    Badge Component Props:
    - style: 'outline', 'dash', 'soft', 'ghost'
    - variant: 'neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error' (maps to color)
    - color: 'neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error' (legacy, use variant)
    - size: 'xs', 'sm', 'md', 'lg', 'xl'
    - class: Additional classes
    - text: Text content (alternative to slot for programmatic rendering)
--}}
@props([
    'style' => null,
    'variant' => null,
    'color' => null,
    'size' => 'md',
    'class' => '',
    'text' => null,
])

@php
    $styleClasses = [
        'outline' => 'badge-outline',
        'dash' => 'badge-dash',
        'soft' => 'badge-soft',
        'ghost' => 'badge-ghost',
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

    $classes = 'badge';

    if ($style && isset($styleClasses[$style])) {
        $classes .= ' ' . $styleClasses[$style];
    }

    // Use variant if provided, otherwise fall back to color (for backward compatibility)
    $colorValue = $variant ?? $color;
    if ($colorValue && isset($colorClasses[$colorValue])) {
        $classes .= ' ' . $colorClasses[$colorValue];
    }

    if (isset($sizeClasses[$size])) {
        $classes .= ' ' . $sizeClasses[$size];
    }

    if (!empty($class)) {
        $classes .= ' ' . $class;
    }

    $classes = trim($classes);
    $finalClass = $classes . ' whitespace-nowrap';
@endphp

<span class="{!! $finalClass !!}" {{ $attributes->except(['style', 'variant', 'color', 'size', 'class', 'text']) }}>
    {{ $text ?? $slot }}
</span>
