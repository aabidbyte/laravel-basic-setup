<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="twoFactorChallenge({
                showRecoveryInput: @js($errors->has('recovery_code'))
            })"
        >
            <div x-show="!showRecoveryInput">
                <x-auth-header
                    :title="__('authentication.two_factor.title')"
                    :description="__('authentication.two_factor.description')"
                ></x-auth-header>
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header
                    :title="__('authentication.two_factor.recovery_title')"
                    :description="__('authentication.two_factor.recovery_description')"
                ></x-auth-header>
            </div>

            <form
                method="POST"
                action="{{ route('two-factor.login.store') }}"
            >
                @csrf

                <div class="space-y-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="form-control">
                            <label
                                for="code"
                                class="label"
                            >
                                <span class="label-text">{{ __('settings.two_factor.setup.otp_label') }}</span>
                            </label>
                            <input
                                type="text"
                                x-model="code"
                                name="code"
                                id="code"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                inputmode="numeric"
                                class="input input-bordered w-full max-w-xs text-center text-2xl tracking-widest @error('code') input-error @enderror"
                                placeholder="000000"
                            />
                            @error('code')
                                <div class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <x-ui.input
                            type="text"
                            name="recovery_code"
                            x-ref="recovery_code"
                            x-bind:required="showRecoveryInput"
                            autocomplete="one-time-code"
                            x-model="recovery_code"
                            :label="__('authentication.two_factor.recovery_code_label')"
                        ></x-ui.input>
                    </div>

                    <x-ui.button
                        type="submit"
                        variant="primary"
                        class="w-full"
                    >
                        {{ __('actions.continue') }}
                    </x-ui.button>
                </div>

                <div class="mt-5 text-center text-sm text-base-content/70">
                    <span>{{ __('authentication.two_factor.switch_to_recovery') }}</span>
                    <x-ui.button
                        type="button"
                        @click="toggleInput()"
                        style="link"
                        color="primary"
                        size="sm"
                    ><span
                            x-show="!showRecoveryInput">{{ __('authentication.two_factor.use_recovery_code') }}</span><span
                            x-show="showRecoveryInput"
                        >{{ __('authentication.two_factor.use_auth_code') }}</span></x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.auth>
