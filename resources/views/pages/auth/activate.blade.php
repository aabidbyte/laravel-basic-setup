<x-layouts.auth>
    @if ($tokenValid)
        <div class="flex flex-col gap-6">
            <x-auth-header
                :title="__('authentication.activation.title')"
                :description="__('authentication.activation.welcome', ['name' => $user->name])"
            ></x-auth-header>

            <x-ui.form
                method="POST"
                action="{{ route('auth.activate', $token) }}"
                class="flex flex-col gap-6"
            >
                @csrf
                <x-ui.password
                    name="password"
                    :label="__('authentication.activation.password_label')"
                    required
                    autofocus
                    autocomplete="new-password"
                    :error="$errors->first('password')"
                    with-strength-meter
                ></x-ui.password>

                <x-ui.password
                    name="password_confirmation"
                    :label="__('authentication.activation.password_confirmation_label')"
                    required
                    autocomplete="new-password"
                    :error="$errors->first('password_confirmation')"
                ></x-ui.password>

                <x-ui.button
                    type="submit"
                    variant="primary"
                    class="w-full"
                >
                    {{ __('authentication.activation.submit') }}
                </x-ui.button>
            </x-ui.form>
        </div>
    @else
        <div class="flex flex-col gap-6 text-center">
             <div class="w-16 h-16 mx-auto bg-error/20 rounded-full flex items-center justify-center">
                <x-ui.icon
                    name="x-mark"
                    class="w-8 h-8 text-error"
                ></x-ui.icon>
            </div>
            <x-auth-header
                :title="__('authentication.activation.invalid_title')"
                :description="__('authentication.activation.invalid_message')"
            ></x-auth-header>

            <x-ui.button
                href="{{ route('login') }}"
                variant="ghost"
                class="w-full"
            >
                {{ __('authentication.activation.back_to_login') }}
            </x-ui.button>
        </div>
    @endif
</x-layouts.auth>
