@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'prependEmpty' => true,
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
    $initialLabel = $options[$initialValue] ?? ($placeholder ?? array_shift($options));
    
    $wireModel = $attributes->wire('model');
@endphp

<div class="flex flex-col gap-1 w-full"
     x-data="customSelect(
         @if ($wireModel && $wireModel->value()) $wire.$entangle('{{ $wireModel->value() }}') @else {{ e(json_encode($initialValue)) }} @endif,
         '{{ json_encode($options, JSON_HEX_APOS) }}',
         '{{ json_encode($placeholder, JSON_HEX_APOS) }}'
     )">

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
    <div x-ref="trigger"
         @click="toggle"
         class="select select-bordered flex items-center justify-between w-full h-auto min-h-12 py-2 cursor-pointer {{ $hasError ? 'select-error' : '' }}"
         id="{{ $selectId }}">
        <span class="truncate"
              x-text="currentLabel || placeholder"></span>
        <x-ui.icon name="chevron-down"
                   class="opacity-50"
                   size="sm"></x-ui.icon>
    </div>

    {{-- Mobile: Bottom Sheet --}}
    <template x-if="isMobile">
        <x-ui.sheet x-model="selectOpen"
                    position="bottom"
                    :title="$label ?? $placeholder">
            <div class="flex flex-col gap-1 w-full">
                <template x-for="(label, val) in options" :key="val">
                    <button type="button"
                            @click="choose(val)"
                            class="btn btn-ghost btn-md w-full justify-start font-normal text-left"
                            :class="{ 'btn-active bg-base-200': value == val }">
                        <span x-text="label" class="truncate"></span>
                        <x-ui.icon name="check" size="sm" class="ml-auto" x-show="value == val"></x-ui.icon>
                    </button>
                </template>
            </div>
        </x-ui.sheet>
    </template>

    {{-- Desktop: Floating Dropdown --}}
    <template x-if="!isMobile">
        <template x-teleport="body">
            <div x-show="selectOpen"
                 x-anchor.bottom-start.offset.4="$refs.trigger"
                 @click.outside="close"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="dropdown-content z-50 min-w-[var(--anchor-width)] max-h-60 overflow-y-auto rounded-box bg-base-100 p-2 shadow-xl border border-base-200"
                 style="display: none;">
                <div class="flex flex-col gap-1">
                    <template x-for="(label, val) in options" :key="val">
                        <button type="button"
                                @click="choose(val)"
                                class="btn btn-ghost btn-sm w-full justify-start font-normal text-left"
                                :class="{ 'btn-active bg-base-200': value == val }">
                            <span x-text="label" class="truncate"></span>
                            <x-ui.icon name="check" size="sm" class="ml-auto" x-show="value == val"></x-ui.icon>
                        </button>
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
                Alpine.data('customSelect', function(value, optionsStr, placeholderStr) {
                    return {
                        value: value,
                        options: JSON.parse(optionsStr),
                        placeholder: JSON.parse(placeholderStr),
                        selectOpen: false,
                        isMobile: window.innerWidth < 1024,
                        currentLabel: '',

                        init: function() {
                            const self = this;
                            this.updateLabel();

                            // Watchers
                            this.$watch('value', function() {
                                self.updateLabel();
                            });
                            this.$watch('options', function() {
                                self.updateLabel();
                            });

                            // Responsive check
                            const updateMobile = function() {
                                self.isMobile = window.innerWidth < 1024;
                            };
                            window.addEventListener('resize', updateMobile);
                            // Initial check
                            updateMobile();
                        },

                        updateLabel: function() {
                            this.currentLabel = (this.options && this.options[this.value]) || this.placeholder;
                        },

                        toggle: function() {
                            this.selectOpen = !this.selectOpen;
                        },

                        close: function() {
                            this.selectOpen = false;
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
