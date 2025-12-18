@props([
    // Style
    'style' => null, // outline, dash, soft, ghost

    // Color
    'color' => null, // neutral, primary, secondary, accent, info, success, warning, error

    // Size
    'size' => 'md', // xs, sm, md, lg, xl

    // Additional classes
    'class' => '',
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

    if ($color && isset($colorClasses[$color])) {
        $classes .= ' ' . $colorClasses[$color];
    }

    if (isset($sizeClasses[$size])) {
        $classes .= ' ' . $sizeClasses[$size];
    }

    if (!empty($class)) {
        $classes .= ' ' . $class;
    }

    $classes = trim($classes);
@endphp

<span {{ $attributes->merge(['class' => $classes])->except(['style', 'color', 'size', 'class']) }}>
    {{ $slot }}
</span>
