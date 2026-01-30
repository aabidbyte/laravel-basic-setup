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

<div class="flex flex-col gap-1">
    @if ($label)
        <x-ui.label :for="$inputId"
                    :text="$label"
                    :required="$required">
            @isset($labelAppend)
                <x-slot:labelAppend>{{ $labelAppend }}</x-slot:labelAppend>
            @endisset
        </x-ui.label>
    @endif

    <div class="relative overflow-visible"
         x-data="passwordVisibility()">
        <input type="password"
               x-ref="input"
               x-bind:type="showPassword ? 'text' : 'password'"
               {{ $attributes->merge(['class' => 'input input-bordered w-full pr-10' . ($hasError ? ' input-error' : '')])->except(['label', 'error', 'type']) }}
               id="{{ $inputId }}" />

        <button type="button"
                @click.stop="toggle()"
                class="btn btn-ghost btn-sm btn-circle absolute right-2 top-1/2 z-10 h-8 min-h-0 w-8 -translate-y-1/2 p-0"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
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
        </button>

        @if ($withGeneration)
            <button type="button"
                    @click.stop="generate()"
                    class="btn btn-ghost btn-sm btn-circle absolute right-10 top-1/2 z-10 h-8 min-h-0 w-8 -translate-y-1/2 p-0"
                    title="{{ __('actions.generate_password') }}"
                    tabindex="0">
                <x-ui.icon name="key"
                           class="h-5 w-5"></x-ui.icon>
            </button>
        @endif
    </div>

    <x-ui.input-error :name="$attributes->get('name')"
                      :error="$error" />

    @if ($withStrengthMeter ?? false)
        <div wire:key="password-strength-{{ $inputId }}">
            <x-ui.password-strength :target-id="$inputId" />
        </div>
    @endif
</div>

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
