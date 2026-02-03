{{--
    Avatar Component Props:
    - imageSrc: Image URL
    - initials: User initials to show if image missing
    - size: 'xs', 'sm', 'md', 'lg', 'xl' (default: 'md')
    - shape: 'circle', 'square' (default: 'circle')
    - class: Additional classes
    - placeholder: Show placeholder styling when no image (default: true)
    - alt: Alternative text for image
--}}
@props([
    'imageSrc' => null,
    'initials' => null,
    'size' => 'md',
    'shape' => 'circle',
    'class' => '',
    'placeholder' => true,
    'alt' => null,
])

@php
    // Size classes
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-16 h-16 text-xl',
        'xl' => 'w-24 h-24 text-2xl',
    ];

    $shapeClasses = [
        'circle' => 'rounded-full',
        'square' => 'rounded-lg',
    ];

    $containerSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $shapeClass = $shapeClasses[$shape] ?? $shapeClasses['circle'];
@endphp

<div class="avatar {{ $placeholder && !$imageSrc ? 'placeholder' : '' }} {{ $class }}">
    <div
         class="{{ $containerSize }} {{ $shapeClass }} {{ $imageSrc ? '' : 'bg-primary text-primary-content' }} flex items-center justify-center">
        @if ($imageSrc)
            <img src="{{ $imageSrc }}"
                 alt="{{ $alt ?? ($initials ?? 'Avatar') }}"
                 class="{{ $shapeClass }} h-full w-full object-cover" />
        @else
            <span>{{ $initials }}</span>
        @endif
    </div>
</div>
