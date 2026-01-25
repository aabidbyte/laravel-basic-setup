{{--
    Base Modal Component
    - Class-based component: App\View\Components\Ui\BaseModal
    - Theme-aware overlay and colors (DaisyUI base-* / *-content)
    - Always uses teleport to render modal in body
--}}
<template x-teleport="body">
    <div x-cloak
         x-show="{{ $openState }}"
         {{ $attributes->merge($containerAttributeDefaults)->class([$containerBaseClasses, $class]) }}>
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
