{{--
    Dropdown Component Props:
    - placement: 'start', 'center', 'end', 'top', 'bottom', 'left', 'right'
    - hover: Enable hover to open dropdown
    - contentClass: Additional classes for dropdown-content
    - bgClass: Background color class (default: bg-base-100)
    - menu: Use menu styling (adds 'menu' class)
    - menuSize: 'xs', 'sm', 'md', 'lg', 'xl'
    - title: Optional title (auto-escaped)
    - triggerText: Optional safe text for trigger (auto-escaped, alternative to slot)
    - items: Optional array of safe text items (auto-escaped, alternative to slot)
    
    SECURITY WARNING:
    - If using slots with user data, ALWAYS use {{ }} NOT {!! !!}
    - For DB content, prefer triggerText/items props (auto-secured)
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
    'triggerText' => null, // Safe alternative to trigger slot
    'items' => null, // Safe alternative to content slot (array of strings)
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

    // Helper closure to render items list
    $renderItems = function () use ($items, $menu) {
        if (empty($items)) {
            return '';
        }
        // Add 'menu' class if not already handled by parent
        $ulClass = $menu ? 'w-full p-0' : 'menu w-full p-0';

        $html = '<ul class="' . $ulClass . '">';
        foreach ($items as $item) {
            // SECURITY: Explicitly escape item text
            $safeItem = e($item);
            $html .= '<li><a>' . $safeItem . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    };
@endphp
<div x-data="responsiveDropdown()"
     class="inline-block"
     {{ $attributes->only('class') }}>
    {{-- Mobile/Tablet Sheet View (< lg) --}}
    <template x-if="$store.ui.isMobile">
        <x-ui.sheet position="bottom"
                    x-model="open"
                    :title="$title">
            @if (isset($trigger) || $triggerText)
                <x-slot:trigger>
                    <div {{ $attributes->except('class') }}>
                        @if (isset($trigger))
                            {{ $trigger }}
                        @else
                            <x-ui.button variant="solid"
                                         size="md"
                                         class="m-1">
                                {{ $triggerText }}
                            </x-ui.button>
                        @endif
                    </div>
                </x-slot:trigger>
            @endif

            @if (!empty($items))
                {!! $renderItems() !!}
            @else
                {{ $slot }}
            @endif

            @isset($actions)
                <x-slot:actions>
                    {{ $actions }}
                </x-slot:actions>
            @endisset
        </x-ui.sheet>
    </template>

    {{-- Desktop Dropdown View (>= lg) --}}
    <template x-if="!$store.ui.isMobile">
        <div class="inline-block"> {{-- Wrapper to ensure inline-block behavior inside template --}}
            @if (!$teleport)
                <div
                     {{ $attributes->except(['placement', 'hover', 'contentClass', 'bgClass', 'menu', 'menuSize', 'teleport', 'aria-label', 'aria-labelledby', 'class'])->merge(['class' => $dropdownClasses]) }}>
                    @if (isset($trigger) || $triggerText)
                        <div tabindex="0"
                             role="button">
                            @if (isset($trigger))
                                {{ $trigger }}
                            @else
                                {{ $triggerText }}
                            @endif
                        </div>
                    @endif
                    <div @class($contentClasses)
                         {{ $attributes->only(['aria-label', 'aria-labelledby']) }}>
                        @if ($title)
                            <div class="menu-title whitespace-nowrap">
                                <span>{{ $title }}</span>
                            </div>
                        @endif
                        @if (!empty($items))
                            {!! $renderItems() !!}
                        @else
                            {{ $slot }}
                        @endif
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
                    @if (isset($trigger) || $triggerText)
                        <div x-ref="trigger"
                             @click="toggle()"
                             role="button">
                            @if (isset($trigger))
                                {{ $trigger }}
                            @else
                                {{ $triggerText }}
                            @endif
                        </div>
                    @endif

                    <template x-teleport="body">
                        <div x-show="open"
                             x-anchor.{{ $alpinePlacement }}.offset.4="$refs.trigger"
                             @click.outside="handleOutside($event)"
                             @click="handleContentClick($event)"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             :style="getDropdownStyle()"
                             @class($contentClasses . ' shadow-xl border border-base-200')
                             {{ $attributes->only(['aria-label', 'aria-labelledby']) }}>
                            @if ($title)
                                <div class="menu-title">
                                    <span>{{ $title }}</span>
                                </div>
                            @endif
                            @if (!empty($items))
                                {!! $renderItems() !!}
                            @else
                                {{ $slot }}
                            @endif
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
                        // isMobile: handled by global store $store.ui.isMobile
                        open: false,
                        // init/destroy no longer needed for resize listener
                    };
                });

                Alpine.data('dropdown', function() {
                    return {
                        open: false,
                        dropdownZIndex: 10000,

                        init: function() {
                            // Initialize z-index stack if not present
                            window.uiZIndexStack = window.uiZIndexStack || {
                                current: 9999,
                                next: function() {
                                    return ++this.current;
                                }
                            };

                            // Detect parent Livewire component
                            const wire = this.$wire || this.$el.closest('[wire\\:id]')?.__livewire;
                            this._wireId = wire?.id;

                            // Close on re-render
                            if (window.Livewire) {
                                this._morphHookRemove = window.Livewire.hook(
                                    'morph.updated',
                                    ({
                                        component
                                    }) => {
                                        if (this.open && component.id === this._wireId) {
                                            this.close();
                                        }
                                    },
                                );
                            }
                        },

                        toggle: function() {
                            this.open = !this.open;
                            if (this.open) {
                                // Get next z-index to appear above modals
                                this.dropdownZIndex = window.uiZIndexStack?.next() || 10000;
                            }
                        },

                        close: function() {
                            this.open = false;
                        },

                        getDropdownStyle: function() {
                            return {
                                zIndex: this.dropdownZIndex
                            };
                        },

                        handleOutside: function(event) {
                            if (this.$refs.trigger && this.$refs.trigger.contains(event.target)) {
                                return;
                            }
                            this.close();
                        },

                        handleContentClick: function(event) {
                            // Close when item is clicked, unless it's a form element
                            const interactiveTagNames = ['INPUT', 'TEXTAREA', 'SELECT', 'LABEL'];
                            if (interactiveTagNames.includes(event.target.tagName)) {
                                return;
                            }

                            this.close();
                        },

                        destroy() {
                            if (this._morphHookRemove) {
                                this._morphHookRemove();
                            }
                            this.open = false;
                            this.dropdownZIndex = 10000;
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
