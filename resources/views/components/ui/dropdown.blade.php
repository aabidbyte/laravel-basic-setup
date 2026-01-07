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
    'teleport' => false,
])

@php
    // Common classes for both modes
    $contentClasses = 'dropdown-content rounded-lg gap-2';

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

    // CSS-only mode (default)
    if (!$teleport) {
        $placementClasses = [
            'start' => 'dropdown-start',
            'center' => 'dropdown-center',
            'end' => 'dropdown-end',
            'top' => 'dropdown-top',
            'bottom' => 'dropdown-bottom',
            'left' => 'dropdown-left',
            'right' => 'dropdown-right',
        ];

        $dropdownClasses = 'dropdown ' . ($placementClasses[$placement] ?? $placementClasses['end']);

        if ($hover) {
            $dropdownClasses .= ' dropdown-hover';
        }
    } else {
        // Alpine Teleport mode
        // We only need basic classes on the wrapper, positioning is handled by Alpine Anchor
        // We derive the Alpine anchor placement from the props
        $alpinePlacement = match ($placement) {
            'start'
                => 'bottom-start', // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // dropdown-start aligns right edge (RTL) but usually means left in LTR? DaisyUI dropdown-start aligns to the start side.
            // DaisyUI: dropdown-end => right aligned to trigger (right:0). Anchor: bottom-end.
            'end' => 'bottom-end',
            'center' => 'bottom',
            'top' => 'top',
            'bottom' => 'bottom',
            'left' => 'left',
            'right' => 'right',
            default => 'bottom-end',
        };
    }
@endphp

@if (!$teleport)
    <div
        {{ $attributes->except(['placement', 'hover', 'contentClass', 'bgClass', 'menu', 'menuSize', 'teleport', 'aria-label', 'aria-labelledby'])->merge(['class' => $dropdownClasses]) }}>
        @isset($trigger)
            <div
                tabindex="0"
                role="button"
            >
                {{ $trigger }}
            </div>
        @endisset
        <div
            tabindex="-1"
            @class($contentClasses)
            {{ $attributes->only(['aria-label', 'aria-labelledby']) }}
        >
            {{ $slot }}
        </div>
    </div>
@else
    <div
        x-data="dropdown()"
        @click.outside="close()"
        class="inline-block"
        {{ $attributes->except(['placement', 'hover', 'contentClass', 'bgClass', 'menu', 'menuSize', 'teleport', 'aria-label', 'aria-labelledby']) }}
    >
        @isset($trigger)
            <div
                x-ref="trigger"
                @click="toggle()"
                role="button"
            >
                {{ $trigger }}
            </div>
        @endisset

        <template x-teleport="body">
            <div
                x-show="open"
                x-anchor.{{ $alpinePlacement }}.offset.4="$refs.trigger"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @class($contentClasses . ' z-50 shadow-xl border border-base-200')
                {{ $attributes->only(['aria-label', 'aria-labelledby']) }}
            >
                {{ $slot }}
            </div>
        </template>
    </div>
@endif
