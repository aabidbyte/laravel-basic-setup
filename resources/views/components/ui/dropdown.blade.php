@props([
    // Placement
    'placement' => 'center', // start, center, end, top, bottom, left, right

    // Behavior
    'hover' => false, // Enable hover to open

    // Content
    'contentClass' => '', // Additional classes for dropdown-content
    'menu' => false, // Use menu styling (adds 'menu' class)
    'menuSize' => 'md', // menu-xs, menu-sm, menu-md, menu-lg, menu-xl

    // Accessibility
    'id' => null, // Optional ID for accessibility
])

@php
    $placementClasses = [
        'start' => 'dropdown-start',
        'center' => 'dropdown-center',
        'end' => 'dropdown-end',
        'top' => 'dropdown-top',
        'bottom' => 'dropdown-bottom',
        'left' => 'dropdown-left',
        'right' => 'dropdown-right',
    ];

    $menuSizeClasses = [
        'xs' => 'menu-xs',
        'sm' => 'menu-sm',
        'md' => '',
        'lg' => 'menu-lg',
        'xl' => 'menu-xl',
    ];

    $dropdownClasses = 'dropdown ' . ($placementClasses[$placement] ?? $placementClasses['end']);

    if ($hover) {
        $dropdownClasses .= ' dropdown-hover';
    }

    $contentClasses = 'dropdown-content';

    if ($menu) {
        $contentClasses .= ' menu';
        if (!empty($menuSizeClasses[$menuSize])) {
            $contentClasses .= ' ' . $menuSizeClasses[$menuSize];
        }
    }

    if (!empty($contentClass)) {
        $contentClasses .= ' ' . $contentClass;
    }

    $dropdownId = $id ?? 'dropdown-' . uniqid();
@endphp

<div class="{{ $dropdownClasses }}" id="{{ $dropdownId }}">
    @isset($trigger)
        <div tabindex="0" role="button"
            {{ $attributes->except(['placement', 'hover', 'contentClass', 'menu', 'menuSize', 'id']) }}>
            {{ $trigger }}
        </div>
    @endisset
    <div tabindex="0" class="{{ $contentClasses }}" {{ $attributes->only(['aria-label', 'aria-labelledby', 'w-fit']) }}>
        {{ $slot }}
    </div>
</div>
