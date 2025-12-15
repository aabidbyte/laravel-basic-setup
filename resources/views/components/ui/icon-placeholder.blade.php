@props([
    'size' => 'sm',
])

@php
    $sizeClasses = [
        'xs' => 'w-4 h-4',
        'sm' => 'w-6 h-6',
        'md' => 'w-8 h-8',
        'lg' => 'w-10 h-10',
        'xl' => 'w-12 h-12',
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<div class="inline-flex items-center justify-center {{ $sizeClass }} {{ $attributes->get('class') }}">
    <span
        class="loading loading-spinner {{ $size === 'xs' ? 'loading-xs' : ($size === 'sm' ? 'loading-sm' : ($size === 'md' ? 'loading-md' : ($size === 'lg' ? 'loading-lg' : 'loading-xl'))) }} text-base-content/30"></span>
</div>
