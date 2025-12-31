{{--
    Dropdown Component Props:
    - placement: 'start', 'center', 'end', 'top', 'bottom', 'left', 'right'
    - hover: Enable hover to open dropdown
    - contentClass: Additional classes for dropdown-content
    - bgClass: Background color class (default: bg-base-100)
    - menu: Use menu styling (adds 'menu' class)
    - menuSize: 'xs', 'sm', 'md', 'lg', 'xl'
    - id: Optional ID for accessibility
--}}
@props([
    'placement' => 'center',
    'hover' => false,
    'contentClass' => '',
    'bgClass' => 'bg-base-100',
    'menu' => false,
    'menuSize' => 'md',
    'id' => null,
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

    $contentClasses = 'dropdown-content rounded-lg gap-2 whitespace-nowrap';

    if (!empty($bgClass)) {
        $contentClasses .= ' ' . $bgClass;
    }

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

<div
    {{ $attributes->except(['placement', 'hover', 'contentClass', 'bgClass', 'menu', 'menuSize', 'id', 'aria-label', 'aria-labelledby'])->merge(['class' => $dropdownClasses, 'id' => $dropdownId]) }}>
    @isset($trigger)
        <div tabindex="0" role="button">
            {{ $trigger }}
        </div>
    @endisset
    <div tabindex="0" @class($contentClasses) {{ $attributes->only(['aria-label', 'aria-labelledby']) }}>
        {{ $slot }}
    </div>
</div>
