@props([
    'active' => false,
])

@php
    $classes = '';

    if ($active) {
        $classes .= ' active';
    }

    $attributes = $attributes->merge(['class' => trim($classes)]);
@endphp

<li {{ $attributes }}>
    {{ $slot }}
</li>
