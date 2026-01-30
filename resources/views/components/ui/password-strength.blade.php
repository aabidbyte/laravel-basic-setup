@props(['targetId'])

<div x-data="passwordStrength('{{ $targetId }}', {
    weak: '{{ __('auth.password_strength.weak') }}',
    good: '{{ __('auth.password_strength.good') }}',
    strong: '{{ __('auth.password_strength.strong') }}'
})"
     class="mt-3 space-y-3">
    {{-- Header with Score Label --}}
    <div class="flex items-center justify-between text-xs"
         x-show="password.length > 0"
         x-cloak
         x-transition>
        <span class="text-base-content/70 font-medium">{{ __('auth.password_strength.title') }}</span>
        <span class="font-bold"
              :class="textColor"
              x-text="label"></span>
    </div>

    {{-- Segmented Progress Bar --}}
    <div class="grid h-1.5 w-full grid-cols-4 gap-1.5">
        <template x-for="i in 4">
            <div class="bg-base-200 rounded-full transition-colors duration-300"
                 :class="{
                     [color]: score >= i
                 }"></div>
        </template>
    </div>

    {{-- Requirements Checklist --}}
    <div class="text-base-content/60 grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    @:class="{ 'text-success font-medium translate-x-1': requirements.length }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.length" />
            <span>{{ __('auth.password_strength.requirements.length') }}</span>
        </x-ui.label>
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    @:class="{ 'text-success font-medium translate-x-1': requirements.lowercase && requirements.uppercase }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.lowercase && requirements.uppercase" />
            <span>{{ __('auth.password_strength.requirements.mixed_case') }}</span>
        </x-ui.label>
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    @:class="{ 'text-success font-medium translate-x-1': requirements.number }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.number" />
            <span>{{ __('auth.password_strength.requirements.number') }}</span>
        </x-ui.label>
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    @:class="{ 'text-success font-medium translate-x-1': requirements.symbol }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.symbol" />
            <span>{{ __('auth.password_strength.requirements.symbol') }}</span>
        </x-ui.label>
    </div>
</div>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('passwordStrength', (targetId, translations = {}) => ({
                    targetId: targetId,
                    translations: translations,
                    password: '',
                    requirements: {
                        length: false,
                        lowercase: false,
                        uppercase: false,
                        number: false,
                        symbol: false,
                    },
                    score: 0,
                    checkInterval: null,
                    inputListener: null,
                    changeListener: null,

                    init() {
                        // Get the target input element
                        const getTargetElement = () =>
                            document.getElementById(this.targetId);

                        // Create bound event handlers so we can remove them later
                        this.inputListener = (event) => {
                            if (event.target.id === this.targetId) {
                                this.checkStrength(event.target.value);
                            }
                        };

                        this.changeListener = (event) => {
                            if (event.target.id === this.targetId) {
                                this.checkStrength(event.target.value);
                            }
                        };

                        // Add event listeners
                        window.addEventListener('input', this.inputListener);
                        window.addEventListener('change', this.changeListener);

                        // Use polling to catch value changes from Livewire
                        // This is the most reliable approach for Livewire wire:model updates
                        this.checkInterval = setInterval(() => {
                            const target = getTargetElement();
                            if (target && target.value !== this.password) {
                                this.checkStrength(target.value);
                            }
                        }, 100); // 100ms polling for responsive updates

                        // Initial check
                        this.$nextTick(() => {
                            const target = getTargetElement();
                            if (target && target.value) {
                                this.checkStrength(target.value);
                            }
                        });
                    },

                    checkStrength(val) {
                        this.password = val;
                        this.requirements.length = val.length >= 8;
                        this.requirements.lowercase = /[a-z]/.test(val);
                        this.requirements.uppercase = /[A-Z]/.test(val);
                        this.requirements.number = /[0-9]/.test(val);
                        this.requirements.symbol = /[^A-Za-z0-9]/.test(val);

                        let s = 0;
                        if (this.requirements.length) s++;
                        if (this.requirements.lowercase && this.requirements.uppercase) s++;
                        if (this.requirements.number) s++;
                        if (this.requirements.symbol) s++;
                        this.score = s;
                    },

                    get label() {
                        if (this.password.length === 0) return '';
                        if (this.score <= 2) return this.translations.weak || 'Weak';
                        if (this.score <= 3) return this.translations.good || 'Good';
                        return this.translations.strong || 'Strong';
                    },

                    get color() {
                        if (this.score <= 2) return 'bg-error';
                        if (this.score <= 3) return 'bg-warning';
                        return 'bg-success';
                    },

                    get textColor() {
                        if (this.score <= 2) return 'text-error';
                        if (this.score <= 3) return 'text-warning';
                        return 'text-success';
                    },

                    destroy() {
                        // Clean up event listeners
                        if (this.inputListener) {
                            window.removeEventListener('input', this.inputListener);
                        }
                        if (this.changeListener) {
                            window.removeEventListener('change', this.changeListener);
                        }

                        // Clear the polling interval
                        if (this.checkInterval) {
                            clearInterval(this.checkInterval);
                        }
                    },
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
