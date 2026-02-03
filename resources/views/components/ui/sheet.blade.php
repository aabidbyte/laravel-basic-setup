{{--
    Sheet (Drawer) Component
    
    A reusable side/bottom panel for secondary content or actions.
    Supports sticky headers/footers, scroll locking, and multiple positions.
    
    Props:
    - position: 'right' (default), 'left', 'top', 'bottom'
    - title: Optional header title
    - width: CSS class for width (side sheets), default: 'w-full sm:w-96'
    - height: CSS class for height (top/bottom sheets), default: 'h-1/2'
    - closeOnBackdrop: bool (default: true)
    - closeOnEscape: bool (default: true)
    - actions: Slot for sticky bottom actions
--}}
@props([
    'position' => 'right',
    'title' => null,
    'width' => 'w-full min-w-sm max-w-lg',
    'height' => 'max-h-[80vh]',
    'closeOnBackdrop' => true,
    'closeOnEscape' => true,
    'open' => null, // Manual open state variable name (as string) or boolean
])

@php
    // Base Classes (Mobile/Tablet Defaults -> Bottom Sheet)
    // We force "Bottom" styling for < lg screens.
    $mobileClasses = 'inset-x-0 bottom-0 h-auto max-h-[85vh] w-full rounded-t-2xl';
    $mobileEnterStart = 'translate-y-full';
    $mobileEnterEnd = 'translate-y-0';
    $mobileLeaveStart = 'translate-y-0';
    $mobileLeaveEnd = 'translate-y-full';

    // Desktop Overrides (>= lg) based on position
    // We must Reset Mobile styles (e.g. bottom-auto, h-full, translate-y-0) and Apply Desktop styles.

    // Default Desktop: Right
    $desktopClasses = match ($position) {
        'left'
            => 'lg:inset-y-0 lg:left-0 lg:right-auto lg:h-full lg:w-auto lg:max-h-full lg:rounded-r-xl lg:rounded-tl-none',
        'top' => 'lg:inset-x-0 lg:top-0 lg:bottom-auto lg:h-auto lg:w-full lg:rounded-b-xl lg:rounded-tr-none',
        'bottom' => 'lg:inset-x-0 lg:bottom-0 lg:h-auto lg:w-full lg:max-h-[85vh] lg:rounded-t-2xl',
        default
            => 'lg:inset-y-0 lg:right-0 lg:left-auto lg:bottom-auto lg:h-full lg:w-auto lg:max-h-full lg:rounded-l-xl lg:rounded-tr-none', // Right
    };

    // Transition Overrides
    $desktopTransition = match ($position) {
        'left' => [
            'enterStart' => 'lg:translate-y-0 lg:-translate-x-full',
            'enterEnd' => 'lg:translate-x-0',
            'leaveStart' => 'lg:translate-x-0',
            'leaveEnd' => 'lg:translate-y-0 lg:-translate-x-full',
        ],
        'top' => [
            'enterStart' => 'lg:-translate-y-full',
            'enterEnd' => 'lg:translate-y-0',
            'leaveStart' => 'lg:translate-y-0',
            'leaveEnd' => 'lg:-translate-y-full',
        ],
        'bottom' => [
            // Same as mobile
            'enterStart' => '',
            'enterEnd' => '',
            'leaveStart' => '',
            'leaveEnd' => '',
        ],
        default => [
            // Right
            'enterStart' => 'lg:translate-y-0 lg:translate-x-full',
            'enterEnd' => 'lg:translate-x-0',
            'leaveStart' => 'lg:translate-x-0',
            'leaveEnd' => 'lg:translate-y-0 lg:translate-x-full',
        ],
    };

    // Merge Classes
    // If the user provided a 'width' class (like w-96), it usually applies to Side Sheets.
    // On Mobile, we force w-full. So we prepend 'lg:' to the width prop if it's meant for desktop?
// Actually, properly written width classes like 'w-full sm:w-96' handle this.
// But our $mobileClasses has 'w-full'.
// If $width is 'w-96', and we have 'w-full w-96', the latter wins.
// So we need to ensure $width only applies on Desktop if it conflicts.
// However, simplest is to let specific classes win or assume user provided responsive width.
// We will append $width/height to the class list.

// Construct final transition strings
$transClasses = [
    'enterStart' => $mobileEnterStart . ' ' . ($desktopTransition['enterStart'] ?? ''),
    'enterEnd' => $mobileEnterEnd . ' ' . ($desktopTransition['enterEnd'] ?? ''),
    'leaveStart' => $mobileLeaveStart . ' ' . ($desktopTransition['leaveStart'] ?? ''),
    'leaveEnd' => $mobileLeaveEnd . ' ' . ($desktopTransition['leaveEnd'] ?? ''),
];

$transitionEnter = 'transform transition ease-in-out duration-300';
$transitionLeave = 'transform transition ease-in-out duration-300';
@endphp

@php
    $wireModel = $attributes->wire('model');
@endphp

<div x-data="sheet({
    openValue: @if($wireModel && $wireModel->value()) $wire.$entangle('{{ $wireModel->value() }}') @else {{ $open ?? 'false' }} @endif,
    closeOnBackdrop: {{ $closeOnBackdrop ? 'true' : 'false' }},
    closeOnEscape: {{ $closeOnEscape ? 'true' : 'false' }}
})"
     x-modelable="open"
     @keydown.escape.window="handleEscape"
     {{ $attributes->merge(['class' => 'inline-block']) }}>

    {{-- Trigger --}}
    @isset($trigger)
        <div @click="openSheet"
             class="inline-block cursor-pointer">
            {{ $trigger }}
        </div>
    @endisset

    {{-- Teleported Modal --}}
    <template x-teleport="body">
        <div x-show="open"
             :style="{ zIndex: zIndex }"
             class="fixed inset-0 overflow-hidden"
             role="dialog"
             aria-modal="true">

            {{-- Backdrop --}}
            <div x-show="open"
                 @click="handleBackdrop"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-base-300/60 backdrop-blur-sm">
            </div>

            {{-- Sheet Panel --}}
            <div x-show="open"
                 x-trap.noscroll="open"
                 x-transition:enter="{{ $transitionEnter }}"
                 x-transition:enter-start="{{ $transClasses['enterStart'] }}"
                 x-transition:enter-end="{{ $transClasses['enterEnd'] }}"
                 x-transition:leave="{{ $transitionLeave }}"
                 x-transition:leave-start="{{ $transClasses['leaveStart'] }}"
                 x-transition:leave-end="{{ $transClasses['leaveEnd'] }}"
                 :style="{ zIndex: zIndex }"
                 @class([
                     'absolute shadow-2xl bg-base-100 flex flex-col z-10',
                     $mobileClasses,
                     $desktopClasses,
                     $width,
                     $height,
                 ])>

                {{-- Header --}}
                @if ($title)
                    <div class="border-base-200 flex shrink-0 items-center justify-between border-b px-3 py-2">
                        <x-ui.title size="md">
                            {{ $title }}
                        </x-ui.title>

                        <x-ui.button @click="close"
                                     circle
                                     variant="ghost"
                                     aria-label="Close">
                            <x-ui.icon name="x-mark"></x-ui.icon>
                        </x-ui.button>
                    </div>
                @else
                    <x-ui.button @click="close"
                                 circle
                                 variant="ghost"
                                 class="absolute right-0 top-0"
                                 aria-label="Close">
                        <x-ui.icon name="x-mark"></x-ui.icon>
                    </x-ui.button>
                @endif

                {{-- Scrollable Content --}}
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    {{ $slot }}
                </div>

                {{-- Sticky Footer (Actions) --}}
                @isset($actions)
                    <div class="border-base-200 bg-base-100 shrink-0 border-t px-6 py-4">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        </div>
    </template>
</div>

@assets
    <script>
        (function() {
            // Global z-index manager
            window.uiZIndexStack = window.uiZIndexStack || {
                current: 9999,
                next: function() {
                    return ++this.current;
                }
            };

            const register = function() {
                Alpine.data('sheet', function(config) {
                    return {
                        open: config.openValue,
                        config: config,
                        zIndex: 9999,

                        init: function() {
                            const self = this;
                            
                            // Initialize z-index if open explicitly on load
                            if (this.open) {
                                this.zIndex = window.uiZIndexStack.next();
                            }

                            this.$watch('open', function(value) {
                                if (value) {
                                    // Bring to front on open
                                    self.zIndex = window.uiZIndexStack.next();
                                    document.body.classList.add('overflow-hidden');
                                } else {
                                    document.body.classList.remove('overflow-hidden');
                                }
                            });
                        },

                        openSheet: function() {
                            this.open = true;
                        },

                        toggle: function() {
                            this.open = !this.open;
                        },

                        close: function() {
                            this.open = false;
                        },

                        handleBackdrop: function() {
                            if (this.config.closeOnBackdrop) {
                                this.close();
                            }
                        },

                        handleEscape: function() {
                            if (this.open && this.config.closeOnEscape) {
                                this.close();
                            }
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
