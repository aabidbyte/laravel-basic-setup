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

    // Construct Alpine x-data expression safely in PHP
    if ($wireModel && $wireModel->value()) {
        $propertyName = e($wireModel->value());
        $entangle = "\$wire.\$entangle(\"{$propertyName}\")";

        if ($wireModel->modifiers()) {
            foreach ($wireModel->modifiers() as $key => $value) {
                if (is_numeric($key)) {
                    $entangle .= ".{$value}";
                } elseif ($value !== true && $value !== null) {
                    $entangle .= ".{$key}.{$value}";
                } else {
                    $entangle .= ".{$key}";
                }
            }
        }
        $valueArg = $entangle;
    } else {
        $valueArg = json_encode(json_encode($initialValue), JSON_HEX_APOS);
    }

    $optionsArray = array_map(fn($val, $label) => [$val, $label], array_keys($options), $options);
    $optionsArg = json_encode(json_encode($optionsArray), JSON_HEX_APOS);
    $placeholderArg = json_encode(json_encode($placeholder), JSON_HEX_APOS);

    $alpineData = "customSelect({$valueArg}, {$optionsArg}, {$placeholderArg})";

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

    // Template for the options list
    // We use HEREDOC with 'blade' tag to avoid PHP interpreting $ or {{ }}
    // We use x-bind: for Alpine expressions so Blade::render doesn't try to evaluate them as PHP
$renderOptionsList = <<<'blade'
<div class="flex w-full flex-col gap-1">
    <template x-for="[val, label] in optionsArray" :key="val">
        <x-ui.button type="button"
                     @click="choose(val)"
                     x-bind:data-selected="isSelected(val) ? 'true' : null"
                     variant="ghost"
                     x-bind:size="$store.ui.isMobile ? 'md' : 'sm'"
                     class="w-full justify-between text-left font-normal"
                     x-bind:class="getOptionClasses(val)">
            <span x-text="label" class="truncate"></span>
            <x-ui.icon name="check"
                       x-show="isSelected(val)"
                       class="ml-auto"
                       size="sm" />
                </x-ui.button>
            </template>
        </div>
blade;
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
            {!! Blade::render($renderOptionsList) !!}
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
                {!! Blade::render($renderOptionsList) !!}
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
                    const optionsArray = typeof options === 'string' ? JSON.parse(options) : options;

                    return {
                        value: value,
                        optionsArray: optionsArray,
                        placeholder: typeof placeholder === 'string' ? JSON.parse(placeholder) :
                            placeholder,
                        selectOpen: false,
                        currentLabel: '',
                        selectWidth: 0,
                        selectZIndex: 10000,

                        init: function() {
                            const self = this;
                            this.updateLabel();

                            window.uiZIndexStack = window.uiZIndexStack || {
                                current: 9999,
                                next: function() {
                                    return ++this.current;
                                }
                            };

                            this.$watch('value', function() {
                                self.updateLabel();
                            });

                            this.$watch('optionsArray', function() {
                                self.updateLabel();
                            });
                        },

                        updateLabel: function() {
                            const option = this.optionsArray.find(([val]) => this.isSelected(val));
                            this.currentLabel = option ? option[1] : (this.placeholder || '');
                        },

                        toggle: function() {
                            this.selectOpen = !this.selectOpen;
                            if (this.selectOpen) {
                                this.selectZIndex = window.uiZIndexStack?.next() || 10000;
                                this.selectWidth = this.$refs.selectTrigger?.offsetWidth || 200;
                                this.scrollToSelected();
                            }
                        },

                        getSelectStyle: function() {
                            return {
                                minWidth: this.selectWidth + 'px',
                                maxWidth: (this.selectWidth + 40) + 'px',
                                zIndex: this.selectZIndex
                            };
                        },

                        scrollToSelected: function() {
                            setTimeout(() => {
                                let searchScope = this.$refs.selectContent || document.body;
                                let selectedBtn = searchScope.querySelector(
                                    '[data-selected="true"]');

                                if (!selectedBtn) {
                                    selectedBtn = document.body.querySelector(
                                        '[data-selected="true"]');
                                }

                                if (selectedBtn) {
                                    const scrollContainer = selectedBtn.closest(
                                        '.overflow-y-auto');

                                    if (scrollContainer) {
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
                            return this.value === val;
                        },

                        getOptionClasses: function(val) {
                            return {
                                'btn-active': this.isSelected(val)
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
