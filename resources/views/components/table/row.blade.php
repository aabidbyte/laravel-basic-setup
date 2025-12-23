@props([
    'selected' => false,
])

@php
    $classes = $attributes->get('class', '');
    // Only add default cursor-pointer if not already set and no wire:click
    if (!str_contains($classes, 'cursor-pointer') && !$attributes->has('wire:click')) {
        $classes .= ' cursor-pointer hover:bg-base-200';
    }
    if ($selected) {
        $classes .= ' bg-base-200';
    }
@endphp

<tr {{ $attributes->merge(['class' => trim($classes)]) }}>
    {{ $slot }}
</tr>

