@props([
    'text' => null,
    'placement' => 'top',
    'offset' => 8,
])

<div x-data="uiTooltip"
     class="relative inline-block"
     @mouseenter="show"
     @mouseleave="hide">

    {{-- Trigger --}}
    <div x-ref="trigger"
         {{ $attributes }}
         @focus="show"
         @blur="hide"
         aria-haspopup="true"
         :aria-expanded="open">
        {{ $slot }}
    </div>

    {{-- Tooltip Content --}}
    <template x-teleport="body">
        <div x-show="open"
             x-anchor.{{ $placement }}.offset.{{ $offset }}="$refs.trigger"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="pointer-events-none z-[9999] rounded bg-gray-900 px-2 py-1 text-xs font-medium text-white shadow-sm dark:bg-gray-700"
             style="display: none;">
            @if ($text)
                {{ $text }}
            @elseif($content ?? false)
                {{ $content }}
            @endif
        </div>
    </template>
</div>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('uiTooltip', () => ({
                    open: false,

                    show() {
                        this.open = true;
                    },

                    hide() {
                        this.open = false;
                    }
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
