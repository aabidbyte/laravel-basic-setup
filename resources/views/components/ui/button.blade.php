@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $variantClasses = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent' => 'btn-accent',
        'neutral' => 'btn-neutral',
        'ghost' => 'btn-ghost',
        'link' => 'btn-link',
        'outline' => 'btn-outline',
        'error' => 'btn-error',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
        'info' => 'btn-info',
    ];

    $sizeClasses = [
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
        'xl' => 'btn-xl',
    ];

    $classes = 'btn ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? '');
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes])->except(['variant', 'size', 'type']) }}>
    {{ $slot }}
</button>
