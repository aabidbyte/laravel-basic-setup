@props([
    'label' => null,
    'title' => null,
    'error' => null,
    'size' => 'md',
    'variant' => 'default',
    'required' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'prependEmpty' => false,
])

@php
    $selectId = $attributes->get('id') ?? uniqid('select-');
    $name = $attributes->get('name');
    $hasError = $error || ($errors->has($name) ?? false);

    if ($prependEmpty && !isset($options[''])) {
        $options = prepend_empty_option($options, $placeholder);
    }

    // Determine initial value and label
    $initialValue = $selected ?? ($attributes->wire('model')->value() ? null : array_key_first($options));
    $initialLabel = $options[$initialValue] ?? ($placeholder ?? (count($options) > 0 ? reset($options) : null));

    $wireModel = $attributes->wire('model');

    // DEBUG: Verify exactly what the component receives
    // dump('SELECT COMPONENT RECEIVED OPTIONS:', $options);

    // Construct Alpine x-data expression safely in PHP
    // We use a single-quoted HTML attribute: x-data='...'

    // 1. Value Argument
    if ($wireModel && $wireModel->value()) {
        $propertyName = e($wireModel->value());
        $entangle = "\$wire.\$entangle(\"{$propertyName}\")";

        // Append all modifiers to entangle (e.g., .live, .debounce.300ms, .lazy, etc.)
        if ($wireModel->modifiers()) {
            foreach ($wireModel->modifiers() as $key => $value) {
                // If key is numeric, value is the modifier name (e.g., [0 => 'live'])
                if (is_numeric($key)) {
                    $entangle .= ".{$value}";
                }
                // If key is string and value is not true/null, it's a modifier with value (e.g., ['debounce' => '300ms'])
            elseif ($value !== true && $value !== null) {
                $entangle .= ".{$key}.{$value}";
            }
            // Otherwise it's a simple modifier (e.g., ['live' => true])
                else {
                    $entangle .= ".{$key}";
                }
            }
        }

        $valueArg = $entangle;
    } else {
        // Double-encode to pass as a string literal: '"value"'
        // JSON_HEX_APOS ensures strict safety inside single-quoted attribute
        $valueArg = json_encode(json_encode($initialValue), JSON_HEX_APOS);
    }

    // 2. Options Argument
    // Convert to indexed array of [value, label] pairs to preserve order
    $optionsArray = array_map(fn($val, $label) => [$val, $label], array_keys($options), $options);
    $optionsArg = json_encode(json_encode($optionsArray), JSON_HEX_APOS);

    // 3. Placeholder Argument
    $placeholderArg = json_encode(json_encode($placeholder), JSON_HEX_APOS);

    $alpineData = "customSelect({$valueArg}, {$optionsArg}, {$placeholderArg})";

    // Closure to render option button (reused in mobile & desktop)
    // SECURITY: All user data (label, val) is bound via x-text, never interpolated directly
    $renderOptionButton = function () {
        return <<<'HTML'
                                <button type="button"
                                        @click="choose(val)"
                                        :data-selected="isSelected(val) ? 'true' : null"
                                        class="btn btn-ghost btn-md lg:btn-sm w-full justify-between text-left font-normal"
                                        :class="getOptionClasses(val)">
                                    <span x-text="label" class="truncate"></span>
                                    <!-- Inline SVG for reactivity within x-for loop -->
                                    <svg x-show="isSelected(val)" class="ml-auto h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
        HTML;
    };

    $sizeClass = match ($size) {
        'xs' => 'select-xs',
        'sm' => 'select-sm',
        'md' => 'select-md',
        'lg' => 'select-lg',
        'xl' => 'select-xl',
        default => 'select-md',
    };

    $variantClass = match ($variant) {
        'ghost' => 'select-ghost',
        default => null,
    };
@endphp

<div class="flex w-full flex-col gap-1"
     x-data='{!! $alpineData !!}'
     wire:key="select-{{ $selectId }}">

    {{-- Label --}}
    @if ($label)
        <x-ui.label :for="$selectId"
                    :text="$label"
                    :required="$required">
            @isset($labelAppend)
                <x-slot:labelAppend>{{ $labelAppend }}</x-slot:labelAppend>
            @endisset
        </x-ui.label>
    @endif

    {{-- Trigger Button --}}
    <div x-ref="selectTrigger"
         @click="toggle"
         @class([
             'select cursor-pointer',
             $sizeClass,
             $variantClass,
             $hasError ? 'select-error' : '',
         ])
         id="{{ $selectId }}">
        <span class="truncate"
              x-text="currentLabel || placeholder"></span>
    </div>

    {{-- Mobile: Bottom Sheet --}}
    <template x-if="$store.ui.isMobile">
        <x-ui.sheet x-model="selectOpen"
                    position="bottom"
                    :title="$label ?? ($placeholder ?? $title)">
            <div class="flex w-full flex-col gap-1">
                <template x-for="[val, label] in optionsArray"
                          :key="val">
                    {!! $renderOptionButton() !!}
                </template>
            </div>
        </x-ui.sheet>
    </template>

    {{-- Desktop: Floating Dropdown --}}
    <template x-if="!$store.ui.isMobile">
        <template x-teleport="body">
            <div x-show="selectOpen"
                 x-anchor.bottom-start.offset.4="$refs.selectTrigger"
                 @click.outside="close"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 x-ref="selectContent"
                 :style="getSelectStyle()"
                 x-cloak
                 class="rounded-box bg-base-100 border-base-200 max-h-60 overflow-y-auto border p-2 shadow-xl">
                <div class="flex flex-col gap-1">
                    <template x-for="[val, label] in optionsArray"
                              :key="val">
                        {!! $renderOptionButton() !!}
                    </template>
                </div>
            </div>
        </template>
    </template>

    {{-- Hidden input for native form submission --}}
    @if ($name)
        <input type="hidden"
               name="{{ $name }}"
               :value="value">
    @endif

    <x-ui.input-error :name="$name"
                      :error="$error" />
</div>

@assets
    <script>
        (function() {
            const register = function() {
                Alpine.data('customSelect', function(value, options, placeholder) {
                    // Options comes as an array of [value, label] pairs from PHP (order preserved)
                    const optionsArray = typeof options === 'string' ? JSON.parse(options) : options;

                    return {
                        value: value,
                        optionsArray: optionsArray,
                        placeholder: typeof placeholder === 'string' ? JSON.parse(placeholder) :
                            placeholder,
                        selectOpen: false,
                        // isMobile: handled by global store $store.ui.isMobile
                        currentLabel: '',
                        selectWidth: 0,
                        selectZIndex: 10000,

                        init: function() {
                            const self = this;
                            this.updateLabel();

                            // Initialize z-index stack if not present
                            window.uiZIndexStack = window.uiZIndexStack || {
                                current: 9999,
                                next: function() {
                                    return ++this.current;
                                }
                            };

                            // Watchers
                            this.$watch('value', function() {
                                self.updateLabel();
                            });

                            this.$watch('optionsArray', function() {
                                self.updateLabel();
                            });

                            // Responsive check
                            // Responsive check
                            // Initial check - handled by global store
                        },

                        destroy: function() {
                            // Cleanup handled by global store
                        },

                        updateLabel: function() {
                            // Find label in array using strict equality
                            const option = this.optionsArray.find(([val]) => this.isSelected(val));
                            console.log()
                            this.currentLabel = option ? option[1] : (this.placeholder || '');
                        },

                        toggle: function() {
                            this.selectOpen = !this.selectOpen;
                            if (this.selectOpen) {
                                // Get next z-index to appear above modals
                                this.selectZIndex = window.uiZIndexStack?.next() || 10000;
                                // Calculate select width from trigger
                                this.selectWidth = this.$refs.selectTrigger?.offsetWidth || 200;
                                this.scrollToSelected();
                            }
                        },

                        getSelectStyle: function() {
                            return {
                                minWidth: this.selectWidth + 'px',
                                maxWidth: (this.selectWidth + 20) + 'px',
                                zIndex: this.selectZIndex
                            };
                        },

                        scrollToSelected: function() {
                            // Wait for DOM to be fully rendered (teleport + x-for)
                            setTimeout(() => {
                                // Search in the correct scope
                                // Desktop: selectContent (teleported), Mobile: inside Sheet (also in body)
                                let searchScope = this.$refs.selectContent || document.body;

                                // Find the selected button using data attribute
                                let selectedBtn = searchScope.querySelector(
                                    '[data-selected="true"]');

                                if (!selectedBtn) {
                                    // Fallback: try searching in document.body
                                    selectedBtn = document.body.querySelector(
                                        '[data-selected="true"]');
                                }

                                if (selectedBtn) {
                                    const scrollContainer = selectedBtn.closest(
                                        '.overflow-y-auto');

                                    if (scrollContainer) {
                                        // Calculate position to center the selected item
                                        const containerRect = scrollContainer
                                            .getBoundingClientRect();
                                        const buttonRect = selectedBtn.getBoundingClientRect();
                                        const scrollTop = scrollContainer.scrollTop + (
                                            buttonRect.top - containerRect.top) - (
                                            containerRect.height / 2) + (buttonRect.height /
                                            2);

                                        scrollContainer.scrollTo({
                                            top: scrollTop,
                                            behavior: 'smooth'
                                        });
                                    } else {
                                        // Fallback to scrollIntoView
                                        selectedBtn.scrollIntoView({
                                            block: 'nearest',
                                            inline: 'nearest',
                                            behavior: 'smooth'
                                        });
                                    }
                                }
                            }, 250);
                        },

                        close: function() {
                            this.selectOpen = false;
                        },

                        isSelected: function(val) {
                            // Use strict equality to distinguish between null, undefined, '', and 0
                            return this.value === val;
                        },

                        getOptionClasses: function(val) {
                            return {
                                'btn-active btn-outline': this.isSelected(val)
                            };
                        },

                        choose: function(val) {
                            this.value = val;
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
