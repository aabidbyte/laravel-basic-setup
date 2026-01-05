{{--
    Title Component Props:
    - level: Heading level (1-6), determines the HTML tag (h1-h6)
    - size: Visual size override: 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl'
    - class: Additional classes
    - subtitle: Optional subtitle text (displays below title)
--}}
@props([
    'level' => 2,
    'size' => null,
    'class' => '',
    'subtitle' => null,
])

@php
    $tag = 'h' . min(max((int) $level, 1), 6);

    // Default sizes based on level
    $defaultSizes = [
        1 => 'text-4xl font-bold',
        2 => 'text-2xl font-semibold',
        3 => 'text-lg font-semibold',
        4 => 'text-base font-medium',
        5 => 'text-sm font-medium',
        6 => 'text-xs font-medium',
    ];

    // Size overrides
    $sizeClasses = [
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'md' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl',
        '2xl' => 'text-2xl',
        '3xl' => 'text-3xl',
        '4xl' => 'text-4xl',
    ];

    $classes =
        $size && isset($sizeClasses[$size])
            ? $sizeClasses[$size] . ' ' . explode(' ', $defaultSizes[$level])[1] // Keep font weight from level
            : $defaultSizes[$level];

    if (!empty($class)) {
        $classes .= ' ' . $class;
    }
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => trim($classes)]) }}>
    {{ $slot }}
    </{{ $tag }}>

    @if ($subtitle)
        <p class="text-base-content/60 text-sm mt-1">{{ $subtitle }}</p>
    @endif
