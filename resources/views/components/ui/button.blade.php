@props([
    'variant' => null, // Deprecated: use 'style' and 'color' instead
    'style' => null, // 'solid', 'outline', 'ghost', 'link', 'soft', 'dash'
    'color' => 'primary', // 'primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'
    'size' => 'md',
    'type' => null,
])

@php
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
            $style = $style ?? $variantMap[$variant]['style'];
            $color = $color ?? $variantMap[$variant]['color'];
        }
    }

    // Default style to 'solid' if not set
    $style = $style ?? 'solid';

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

    $classes = trim("btn {$styleClass} {$colorClass} {$sizeClass}");
@endphp

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes])->except(['variant', 'style', 'color', 'size', 'type']) }}>
    {{ $slot }}
</button>
