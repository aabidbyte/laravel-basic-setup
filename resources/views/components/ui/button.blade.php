{{--
    Button Component Props:
    - variant: Deprecated - use 'style' and 'color' instead
    - style: 'solid', 'outline', 'ghost', 'link', 'soft', 'dash'
    - color: 'primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'
    - size: 'xs', 'sm', 'md', 'lg', 'xl'
    - type: HTML button type attribute
    - circle: boolean - makes the button circular
--}}
@props([
    'variant' => null,
    'style' => null,
    'color' => null,
    'size' => 'md',
    'type' => null,
    'text' => null,
    'circle' => false,
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
            'ghost' => ['style' => 'ghost'],
            'link' => ['style' => 'link'],
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
        if (isset($variantMap[$variant]['color'])) {
            $color = $variantMap[$variant]['color'];
        } else {
            $color = $originalColor;
        }
    }
}

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

$styleClass = isset($style) ? $styleClasses[$style] : '';
$colorClass = isset($color) ? $colorClasses[$color] : '';
$sizeClass = isset($size) ? $sizeClasses[$size] : '';
$circleClass = $circle ? 'btn-circle' : '';
    $btnClasses = "{$styleClass} {$colorClass} {$sizeClass} {$circleClass}";

@endphp

@if ($href ?? false)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => trim("btn {$btnClasses}")])->except(['variant', 'style', 'color', 'size', 'type', 'text', 'href']) }}
    >
        {{ $text ?? $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => trim("btn {$btnClasses}")])->except(['variant', 'style', 'color', 'size', 'type', 'text']) }}
    >
        {{ $text ?? $slot }}
    </button>
@endif
