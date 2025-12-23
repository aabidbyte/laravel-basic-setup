{{--
    Button Component Props:
    - variant: Deprecated - use 'style' and 'color' instead
    - style: 'solid', 'outline', 'ghost', 'link', 'soft', 'dash'
    - color: 'primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'
    - size: 'xs', 'sm', 'md', 'lg', 'xl'
    - type: HTML button type attribute
--}}
@props([
    'variant' => null,
    'style' => null,
    'color' => null,
    'size' => 'md',
    'type' => null,
])

@php
    // Store original color to check if it was explicitly provided
    $originalColor = $color;

    // Backward compatibility: if variant is set, map it to style/color
    if ($variant !== null) {
        $variantMap = [
            'primary' => ['style' => 'solid', 'color' => 'primary'],
            'secondary' => ['style' => 'solid', 'color' => 'secondary'],
            'accent' => ['style' => 'solid', 'color' => 'accent'],
            'neutral' => ['style' => 'solid', 'color' => 'neutral'],
            'ghost' => ['style' => 'ghost', 'color' => 'primary'],
            'link' => ['style' => 'link', 'color' => 'primary'],
            'outline' => ['style' => 'outline', 'color' => 'primary'],
            'error' => ['style' => 'solid', 'color' => 'error'],
            'success' => ['style' => 'solid', 'color' => 'success'],
            'warning' => ['style' => 'solid', 'color' => 'warning'],
            'info' => ['style' => 'solid', 'color' => 'info'],
        ];

        if (isset($variantMap[$variant])) {
            // Only override style/color if they weren't explicitly provided
        $style = $style ?? $variantMap[$variant]['style'];
        // If color wasn't explicitly provided, use variant's color
        // This ensures variant="error" defaults to color="error" even if color prop has a default
        $color = $originalColor ?? $variantMap[$variant]['color'];
    }
}

// Default style to 'solid' if not set
$style = $style ?? 'solid';

// Default color to 'primary' if not set (after variant mapping)
$color = $color ?? 'primary';

$styleClasses = [
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

$styleClass = $styleClasses[$style] ?? '';
$colorClass = $colorClasses[$color] ?? $colorClasses['primary'];
$sizeClass = $sizeClasses[$size] ?? '';

@endphp

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => trim("btn {$styleClass} {$colorClass} {$sizeClass}")])->except(['variant', 'style', 'color', 'size', 'type']) }}>
    {{ $slot }}
</button>
