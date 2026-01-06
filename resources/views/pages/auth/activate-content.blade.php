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
                    <x-ui.title
                        level="2"
                        class="justify-center"
                    >{{ __('ui.auth.activation.success_title') }}</x-ui.title>
                    <p class="text-base-content/70">{{ __('ui.auth.activation.success_message') }}</p>
                    <x-ui.button
                        href="{{ route('login') }}"
                        variant="primary"
                        class="w-full"
                    >
                        {{ __('ui.auth.activation.login_button') }}
                    </x-ui.button>
                </div>
            @elseif ($tokenValid && $user)
                {{-- Activation form --}}
                <div class="text-center mb-6">
                    <x-ui.title
                        level="2"
                        class="justify-center"
                    >{{ __('ui.auth.activation.title') }}</x-ui.title>
                    <p class="text-base-content/70 mt-2">
                        {{ __('ui.auth.activation.welcome', ['name' => $user->name]) }}
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
                        :label="__('ui.auth.activation.password_label')"
                        required
                        autofocus
                        autocomplete="new-password"
                    ></x-ui.input>

                    <x-ui.input
                        type="password"
                        wire:model="password_confirmation"
                        name="password_confirmation"
                        :label="__('ui.auth.activation.password_confirmation_label')"
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
                            {{ __('ui.auth.activation.submit') }}
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
                    <x-ui.title
                        level="2"
                        class="justify-center"
                    >{{ __('ui.auth.activation.invalid_title') }}</x-ui.title>
                    <p class="text-base-content/70">{{ __('ui.auth.activation.invalid_message') }}</p>
                    <x-ui.button
                        href="{{ route('login') }}"
                        variant="ghost"
                    >
                        {{ __('ui.auth.activation.back_to_login') }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </div>
</div>
