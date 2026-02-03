{{--
    Base Modal Component
    - Class-based component: App\View\Components\Ui\BaseModal
    - Theme-aware overlay and colors (DaisyUI base-* / *-content)
    - Always uses teleport to render modal in body
--}}
<div x-data="responsiveOverlay('{{ $openState }}', {{ ($open || $autoOpen) ? 'true' : 'false' }}, {{ $useParentState ? 'true' : 'false' }})"
     class="inline-block">
    {{-- Mobile View (< lg): Bottom Sheet --}}
    <template x-if="isMobile">
        <x-ui.sheet x-model="{{ $openState }}"
                    :title="$title"
                    :close-on-backdrop="$closeOnOutsideClick"
                    :close-on-escape="$closeOnEscape">
            <div class="{{ $bodyClass }}">
                {{ $slot }}
            </div>

            @if ($showFooter && (isset($footerActions) || isset($actions)))
                <x-slot:actions>
                    <div class="flex flex-row-reverse gap-2">
                        {{ $footerActions ?? $actions }}
                    </div>
                </x-slot:actions>
            @endif
        </x-ui.sheet>
    </template>

    {{-- Desktop View (>= lg): Original Modal --}}
    <template x-if="!isMobile">
        <template x-teleport="body">
            <div x-cloak
                 x-show="{{ $openState }}"
                 :style="{ zIndex: zIndex }"
                 {{ $attributes->merge(collect($containerAttributeDefaults)->except('x-data')->toArray())->class([$containerBaseClasses, $class]) }}>
                <div x-show="{{ $openState }}"
                     {{ $dialogAttributes }}
                     class="{{ $dialogClasses }}">
                    @if ($title || $showCloseButton)
                        <div class="{{ $headerClass }} {{ $paddingClass }} flex items-center justify-between gap-4">
                            @if ($title)
                                <x-ui.title level="3"
                                            id="{{ $titleId }}"
                                            class="text-lg font-bold">
                                    {{ $title }}
                                </x-ui.title>
                            @else
                                <div></div>
                            @endif

                            @if ($showCloseButton)
                                <x-ui.button type="button"
                                             x-on:click="{{ $closeAction }}"
                                             variant="ghost"
                                             size="sm"
                                             circle
                                             class="{{ $closeButtonClass }}"
                                             aria-label="{{ $closeButtonLabel }}">
                                    <x-ui.icon name="x-mark"
                                               size="sm"
                                               aria-hidden="true"></x-ui.icon>
                                </x-ui.button>
                            @endif
                        </div>
                    @endif

                    @if ($description)
                        <x-ui.description id="{{ $descriptionId }}"
                                          class="{{ $paddingClass }} mt-2">
                            {{ $description }}
                        </x-ui.description>
                    @endif

                    <div class="{{ $bodyClass }} {{ $paddingClass }}">
                        {{ $slot }}
                    </div>

                    @if ($showFooter && (isset($footerActions) || isset($actions)))
                        <div
                             class="{{ $footerClass }} {{ $backgroundClass }} {{ $paddingClass }} sticky inset-0 bottom-0 flex flex-row-reverse">
                            @isset($footerActions)
                                {{ $footerActions }}
                            @endisset

                            @isset($actions)
                                {{ $actions }}
                            @endisset
                        </div>
                    @endif
                </div>
            </div>
        </template>
    </template>
</div>

@assets
    <script>
        (function() {
            // Global z-index manager (shared definition)
            window.uiZIndexStack = window.uiZIndexStack || {
                current: 9999,
                next: function() {
                    return ++this.current;
                }
            };

            const register = function() {
                Alpine.data('responsiveOverlay', function(openState, initialOpen, useParentState) {
                    const data = {
                        isMobile: window.innerWidth < 1024,
                        zIndex: 9999,
                        
                        init: function() {
                            const self = this;
                            
                            // Responsive check
                            const update = function() {
                                self.isMobile = window.innerWidth < 1024;
                            };
                            window.addEventListener('resize', update);
                            update();

                            // Logic to bring to front on open
                            // We watch the dynamic property name 'openState'
                            this.$watch(openState, function(value) {
                                if (value) {
                                    self.zIndex = window.uiZIndexStack.next();
                                }
                            });

                            // If initially open
                            if (this[openState] || (initialOpen && !useParentState)) {
                                this.zIndex = window.uiZIndexStack.next();
                            }
                        }
                    };

                    if (!useParentState && openState) {
                        data[openState] = initialOpen;
                    }

                    return data;
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
