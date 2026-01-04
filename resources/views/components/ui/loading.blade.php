{{--
    Loading Spinner Component
    
    Props:
    - size: xs, sm, md, lg, xl (default: lg)
    - variant: spinner, dots, ring, ball, bars, infinity (default: spinner)
    - color: primary, secondary, accent, neutral, info, success, warning, error (default: primary)
    - centered: bool - wrap in flex container with centering (default: true)
    - padding: tailwind padding class for centered container (default: py-12)
--}}
@props([
    'size' => 'lg',
    'variant' => 'spinner',
    'color' => 'primary',
    'centered' => true,
    'padding' => 'py-12',
])

@php
    $sizeClasses = [
        'xs' => 'loading-xs',
        'sm' => 'loading-sm',
        'md' => 'loading-md',
        'lg' => 'loading-lg',
        'xl' => 'loading-xl',
    ];
    
    $variantClasses = [
        'spinner' => 'loading-spinner',
        'dots' => 'loading-dots',
        'ring' => 'loading-ring',
        'ball' => 'loading-ball',
        'bars' => 'loading-bars',
        'infinity' => 'loading-infinity',
    ];
    
    $colorClasses = [
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'accent' => 'text-accent',
        'neutral' => 'text-neutral',
        'info' => 'text-info',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-error',
    ];
    
    $loadingClass = implode(' ', [
        'loading',
        $variantClasses[$variant] ?? 'loading-spinner',
        $sizeClasses[$size] ?? 'loading-lg',
        $colorClasses[$color] ?? 'text-primary',
    ]);
@endphp

@if ($centered)
    <div {{ $attributes->merge(['class' => "flex items-center justify-center {$padding}"]) }}>
        <span class="{{ $loadingClass }}"></span>
    </div>
@else
    <span {{ $attributes->merge(['class' => $loadingClass]) }}></span>
@endif
