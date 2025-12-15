@props([
    'size' => 'sm', // Size: xs, sm, md, lg, xl, or custom Tailwind class
    'class' => '', // Additional classes
])

@php
    $sizeClasses = [
        'xs' => 'w-4 h-4',
        'sm' => 'w-5 h-5',
        'md' => 'w-6 h-6',
        'lg' => 'w-8 h-8',
        'xl' => 'w-10 h-10',
    ];

    // If size is a custom class, use it; otherwise use predefined size
    $sizeClass = isset($sizeClasses[$size]) ? $sizeClasses[$size] : $size;
    $iconClass = trim("{$sizeClass} {$class}");
@endphp

{{-- SVG wrapper for inline icons --}}
<svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"
    {{ $attributes->except(['size', 'class']) }}>
    {{ $slot }}
</svg>
