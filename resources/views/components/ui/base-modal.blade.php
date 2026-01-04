{{--
    Base Modal Component
    - Class-based component: App\View\Components\Ui\BaseModal
    - Theme-aware overlay and colors (DaisyUI base-* / *-content)
--}}

<div
    x-cloak
    x-show="{{ $openState }}"
    {{ $attributes->merge($containerAttributeDefaults)->class([$containerBaseClasses, $class]) }}
>
    <div
        x-show="{{ $openState }}"
        {{ $dialogAttributes }}
        class="{{ $dialogClasses }}"
    >
        @if ($title || $showCloseButton)
            <div class="flex items-center justify-between gap-4 {{ $headerClass }}">
                @if ($title)
                    <h3
                        id="{{ $titleId }}"
                        class="text-lg font-bold"
                    >
                        {{ $title }}
                    </h3>
                @else
                    <div></div>
                @endif

                @if ($showCloseButton)
                    <button
                        type="button"
                        x-on:click="{{ $closeAction }}"
                        class="btn btn-sm btn-circle btn-ghost {{ $closeButtonClass }}"
                        aria-label="{{ $closeButtonLabel }}"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                            stroke="currentColor"
                            fill="none"
                            stroke-width="1.4"
                            class="w-5 h-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        @if ($description)
            <p
                id="{{ $descriptionId }}"
                class="mt-2 text-sm text-base-content/70"
            >
                {{ $description }}
            </p>
        @endif

        <div class="{{ $bodyClass }}">
            {{ $slot }}
        </div>

        @if ($showFooter && (isset($footerActions) || isset($actions)))
            <div class="modal-action {{ $footerClass }}">
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
