<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="card bg-base-100 shadow-xl w-full max-w-md">
        <div class="card-body">
            @if ($activated)
                {{-- Success state --}}
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 mx-auto bg-success/20 rounded-full flex items-center justify-center">
                        <x-ui.icon
                            name="check"
                            class="w-8 h-8 text-success"
                        ></x-ui.icon>
                    </div>
                    <h2 class="card-title justify-center text-2xl">{{ __('auth.activation.success_title') }}</h2>
                    <p class="text-base-content/70">{{ __('auth.activation.success_message') }}</p>
                    <a
                        href="{{ route('login') }}"
                        class="btn btn-primary w-full"
                    >
                        {{ __('auth.activation.login_button') }}
                    </a>
                </div>
            @elseif ($tokenValid && $user)
                {{-- Activation form --}}
                <div class="text-center mb-6">
                    <h2 class="card-title justify-center text-2xl">{{ __('auth.activation.title') }}</h2>
                    <p class="text-base-content/70 mt-2">
                        {{ __('auth.activation.welcome', ['name' => $user->name]) }}
                    </p>
                </div>

                <x-ui.form
                    wire:submit="activateAccount"
                    class="space-y-4"
                >
                    <x-ui.input
                        type="password"
                        wire:model="password"
                        name="password"
                        :label="__('auth.activation.password_label')"
                        required
                        autofocus
                        autocomplete="new-password"
                    ></x-ui.input>

                    <x-ui.input
                        type="password"
                        wire:model="password_confirmation"
                        name="password_confirmation"
                        :label="__('auth.activation.password_confirmation_label')"
                        required
                        autocomplete="new-password"
                    ></x-ui.input>

                    <div class="pt-4">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            class="w-full"
                        >
                            <x-ui.loading
                                wire:loading
                                wire:target="activateAccount"
                                size="sm"
                            ></x-ui.loading>
                            {{ __('auth.activation.submit') }}
                        </x-ui.button>
                    </div>
                </x-ui.form>
            @else
                {{-- Invalid/expired token --}}
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 mx-auto bg-error/20 rounded-full flex items-center justify-center">
                        <x-ui.icon
                            name="x-mark"
                            class="w-8 h-8 text-error"
                        ></x-ui.icon>
                    </div>
                    <h2 class="card-title justify-center text-2xl">{{ __('auth.activation.invalid_title') }}</h2>
                    <p class="text-base-content/70">{{ __('auth.activation.invalid_message') }}</p>
                    <a
                        href="{{ route('login') }}"
                        class="btn btn-ghost"
                    >
                        {{ __('auth.activation.back_to_login') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
