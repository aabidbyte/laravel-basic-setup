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
    'title' => null,
])

@php
    // Common classes for both modes
    $menuSizeClasses = [
        'xs' => 'menu-xs',
        'sm' => 'menu-sm',
        'md' => 'menu-md',
        'lg' => 'menu-lg',
        'xl' => 'menu-xl',
    ];

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
        $contentClasses .= ' ' . $contentClasses;
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
        // We logic remains the same, but we wrap it in x-if
        $alpinePlacement = match ($placement) {
            'start' => 'bottom-start',
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
<div x-data="responsiveDropdown()"
     class="inline-block"
     {{ $attributes->only('class') }}>
    {{-- Mobile/Tablet Sheet View (< lg) --}}
    <template x-if="isMobile">
        <x-ui.sheet position="bottom"
                    x-model="open"
                    :title="$title">
            @isset($trigger)
                <x-slot:trigger>
                    <div {{ $attributes->except('class') }}>
                        {{ $trigger }}
                    </div>
                </x-slot:trigger>
            @endisset

            {{ $slot }}

            @isset($actions)
                <x-slot:actions>
                    {{ $actions }}
                </x-slot:actions>
            @endisset
        </x-ui.sheet>
    </template>

    {{-- Desktop Dropdown View (>= lg) --}}
    <template x-if="!isMobile">
        <div class="inline-block"> {{-- Wrapper to ensure inline-block behavior inside template --}}
            @if (!$teleport)
                <div
                     {{ $attributes->except(['placement', 'hover', 'contentClass', 'bgClass', 'menu', 'menuSize', 'teleport', 'aria-label', 'aria-labelledby', 'class'])->merge(['class' => $dropdownClasses]) }}>
                    @isset($trigger)
                        <div tabindex="0"
                             role="button">
                            {{ $trigger }}
                        </div>
                    @endisset
                    <div @class($contentClasses)
                         {{ $attributes->only(['aria-label', 'aria-labelledby']) }}>
                        @if ($title)
                            <div class="menu-title whitespace-nowrap">
                                <span>{{ $title }}</span>
                            </div>
                        @endif
                        {{ $slot }}
                        @isset($actions)
                            <div class="border-base-200 mt-2 border-t pt-2">
                                {{ $actions }}
                            </div>
                        @endisset
                    </div>
                </div>
            @else
                <div x-data="dropdown()"
                     class="inline-block"
                     {{ $attributes->except(['placement', 'hover', 'contentClass', 'bgClass', 'menu', 'menuSize', 'teleport', 'aria-label', 'aria-labelledby', 'class']) }}>
                    @isset($trigger)
                        <div x-ref="trigger"
                             @click="toggle()"
                             role="button">
                            {{ $trigger }}
                        </div>
                    @endisset

                    <template x-teleport="body">
                        <div x-show="open"
                             x-anchor.{{ $alpinePlacement }}.offset.4="$refs.trigger"
                             @click.outside="handleOutside($event)"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             @class($contentClasses . ' z-50 shadow-xl border border-base-200')
                             {{ $attributes->only(['aria-label', 'aria-labelledby']) }}>
                            @if ($title)
                                <div class="menu-title">
                                    <span>{{ $title }}</span>
                                </div>
                            @endif
                            {{ $slot }}
                            @isset($actions)
                                <div class="border-base-200 mt-2 border-t pt-2">
                                    {{ $actions }}
                                </div>
                            @endisset
                        </div>
                    </template>
                </div>
            @endif
        </div>
    </template>
</div>

@assets
    <script>
        (function() {
            const register = function() {
                Alpine.data('responsiveDropdown', function() {
                    return {
                        isMobile: window.innerWidth < 1024,
                        open: false,
                        init: function() {
                            const self = this;
                            const update = function() {
                                self.isMobile = window.innerWidth < 1024;
                            };
                            window.addEventListener('resize', update);
                            update();
                        }
                    };
                });

                Alpine.data('dropdown', function() {
                    return {
                        open: false,

                        toggle: function() {
                            this.open = !this.open;
                        },

                        close: function() {
                            this.open = false;
                        },

                        handleOutside: function(event) {
                            if (this.$refs.trigger && this.$refs.trigger.contains(event.target)) {
                                return;
                            }
                            this.close();
                        }
                    };
                });
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
