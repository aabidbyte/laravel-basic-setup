{{--
    Subtitle Component Props:
    - tag: HTML tag to use (default: p)
    - class: Additional classes
--}}
@props([
    'tag' => 'p',
    'class' => '',
])

@php
    $baseClasses = 'text-base-content/60 mt-1 text-sm';
    $classes = trim($baseClasses . ' ' . $class);
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
    </{{ $tag }}>
