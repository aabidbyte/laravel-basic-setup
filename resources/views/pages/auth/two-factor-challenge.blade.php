<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div class="relative w-full h-auto" x-cloak x-data="{
            showRecoveryInput: @js($errors->has('recovery_code')),
            code: '',
            recovery_code: '',
            toggleInput() {
                this.showRecoveryInput = !this.showRecoveryInput;
                this.code = '';
                this.recovery_code = '';
                $dispatch('clear-2fa-auth-code');
                $nextTick(() => {
                    this.showRecoveryInput ? this.$refs.recovery_code?.focus() : $dispatch('focus-2fa-auth-code');
                });
            },
        }">
            <div x-show="!showRecoveryInput">
                <x-auth-header :title="__('Authentication Code')" :description="__('Enter the authentication code provided by your authenticator application.')" />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header :title="__('Recovery Code')" :description="__(
                    'Please confirm access to your account by entering one of your emergency recovery codes.',
                )" />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}">
                @csrf

                <div class="space-y-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="form-control">
                            <label for="code" class="label">
                                <span class="label-text">{{ __('OTP Code') }}</span>
                            </label>
                            <input type="text" x-model="code" name="code" id="code" maxlength="6"
                                pattern="[0-9]{6}" inputmode="numeric"
                                class="input input-bordered w-full max-w-xs text-center text-2xl tracking-widest @error('code') input-error @enderror"
                                placeholder="000000" />
                            @error('code')
                                <div class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <x-ui.input type="text" name="recovery_code" x-ref="recovery_code"
                            x-bind:required="showRecoveryInput" autocomplete="one-time-code" x-model="recovery_code"
                            :label="__('Recovery Code')" />
                    </div>

                    <x-ui.button type="submit" variant="primary" class="w-full">
                        {{ __('Continue') }}
                    </x-ui.button>
                </div>

                <div class="mt-5 text-center text-sm text-base-content/70">
                    <span>{{ __('or you can') }}</span>
                    <button type="button" @click="toggleInput()" class="link link-primary">
                        <span x-show="!showRecoveryInput">{{ __('login using a recovery code') }}</span>
                        <span x-show="showRecoveryInput">{{ __('login using an authentication code') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.auth>
