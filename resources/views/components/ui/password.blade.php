@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'withGeneration' => false,
])

@php
    $inputId = $attributes->get('id') ?? uniqid('password-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);
@endphp

<x-ui.input {{ $attributes->merge(['class' => 'pr-10'])->except(['label', 'error', 'type']) }}
            type="password"
            x-ref="input"
            :id="$inputId"
            :label="$label"
            :error="$error"
            :required="$required"
            container-class="relative overflow-visible"
            x-data="passwordVisibility()">
    <x-slot:append>
        <div class="flex items-center gap-1">
            @if ($withGeneration)
                <x-ui.button type="button"
                             variant="ghost"
                             size="sm"
                             circle
                             @click.stop="generate()"
                             class="p-0"
                             title="{{ __('actions.generate_password') }}"
                             tabindex="0">
                    <x-ui.icon name="key"
                               class="h-5 w-5"></x-ui.icon>
                </x-ui.button>
            @endif

            <x-ui.button type="button"
                         variant="ghost"
                         size="sm"
                         circle
                         @click.stop="toggle()"
                         class="p-0"
                         x-bind:aria-label="showPassword ? '{{ __('actions.hide_password') }}' : '{{ __('actions.show_password') }}'"
                         tabindex="0">
                <span x-show="!showPassword"
                      x-cloak>
                    <x-ui.icon name="eye"
                               class="h-5 w-5"></x-ui.icon>
                </span>
                <span x-show="showPassword"
                      x-cloak>
                    <x-ui.icon name="eye-slash"
                               class="h-5 w-5"></x-ui.icon>
                </span>
            </x-ui.button>
        </div>
    </x-slot:append>

    @if ($withStrengthMeter ?? false)
        <div wire:key="password-strength-{{ $inputId }}">
            <x-ui.password-strength :target-id="$inputId" />
        </div>
    @endif
</x-ui.input>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('passwordVisibility', () => ({
                    showPassword: false,

                    toggle() {
                        this.showPassword = !this.showPassword;
                    },

                    generate() {
                        const length = 16;
                        const charset = {
                            upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                            lower: 'abcdefghijklmnopqrstuvwxyz',
                            number: '0123456789',
                            symbol: '!@#$%^&*()_+~{}[]:;?<>.,/',
                        };

                        let password = '';

                        // Ensure at least one of each type
                        password +=
                            charset.upper[Math.floor(Math.random() * charset.upper.length)];
                        password +=
                            charset.lower[Math.floor(Math.random() * charset.lower.length)];
                        password +=
                            charset.number[
                                Math.floor(Math.random() * charset.number.length)
                            ];
                        password +=
                            charset.symbol[
                                Math.floor(Math.random() * charset.symbol.length)
                            ];

                        // Fill the rest randomly
                        const allChars = Object.values(charset).join('');
                        for (let i = password.length; i < length; i++) {
                            password +=
                                allChars[Math.floor(Math.random() * allChars.length)];
                        }

                        // Shuffle results
                        password = password
                            .split('')
                            .sort(() => 0.5 - Math.random())
                            .join('');

                        // Set value and trigger events for Livewire/Alpine using $refs
                        if (this.$refs.input) {
                            this.$refs.input.value = password;
                            this.$refs.input.dispatchEvent(
                                new Event('input', {
                                    bubbles: true
                                }),
                            );
                        }

                        // Show password so the user can see it
                        this.showPassword = true;

                        // Optional: notify user (if Notification exists)
                        if (window.NotificationBuilder) {
                            window.NotificationBuilder.make()
                                .title('Password Generated')
                                .success()
                                .send();
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
