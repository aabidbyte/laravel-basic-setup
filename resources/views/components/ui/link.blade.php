{{--
    Link Component Props:
    - href: URL to link to (required)
    - color: 'primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error' (default: 'primary')
    - underline: boolean - always show underline (default: false, shows on hover)
    - class: Additional classes
    - navigate: boolean - use wire:navigate for SPA navigation (default: true)
--}}
@props([
    'href' => '#',
    'color' => 'primary',
    'underline' => false,
    'class' => '',
    'navigate' => true,
])

@php
    $colorClasses = [
        'primary' => 'link-primary',
        'secondary' => 'link-secondary',
        'accent' => 'link-accent',
        'neutral' => 'link-neutral',
        'info' => 'link-info',
        'success' => 'link-success',
        'warning' => 'link-warning',
        'error' => 'link-error',
    ];

    $colorClass = $colorClasses[$color] ?? $colorClasses['primary'];
    $underlineClass = $underline ? '' : 'link-hover';

    $classes = "link {$colorClass} {$underlineClass}";
    if (!empty($class)) {
        $classes .= ' ' . $class;
    }
@endphp

<a href="{{ $href }}"
   @if ($navigate) wire:navigate @endif
   {{ $attributes->merge(['class' => trim($classes)]) }}>
    {{ $slot }}
</a>
