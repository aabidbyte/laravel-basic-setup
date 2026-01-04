<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('ui.auth.login.title')"
            :description="__('ui.auth.login.description')"
        ></x-auth-header>

        <x-ui.form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col"
        >
            @csrf

            {{-- Production: Show text input; Dev: Show user select dropdown --}}
            @if (isProduction())
                <x-ui.input
                    type="text"
                    name="identifier"
                    :label="__('ui.auth.login.email_label')"
                    :value="old('identifier')"
                    required
                    autofocus
                    autocomplete="username"
                    :placeholder="__('ui.auth.login.email_placeholder')"
                ></x-ui.input>
                <x-ui.password
                    name="password"
                    :label="__('ui.auth.login.password_label')"
                    required
                    autocomplete="current-password"
                    :placeholder="__('ui.auth.login.password_placeholder')"
                >
                    @if (Route::has('password.request'))
                        <x-slot:label-append>
                            <a
                                href="{{ route('password.request') }}"
                                wire:navigate
                                class="label-text-alt link"
                            >
                                {{ __('ui.auth.login.forgot_password') }}
                            </a>
                        </x-slot:label-append>
                    @endif
                </x-ui.password>
            @else
                {{-- Dev mode: Hide password field and use default password --}}
                <x-ui.select
                    name="identifier"
                    :label="__('ui.auth.login.email_label')"
                    :options="$users"
                    :selected="old('identifier')"
                    :placeholder="__('ui.auth.login.select_user')"
                    :error="$errors->first('identifier')"
                    required
                    autofocus
                />
                <input
                    type="hidden"
                    name="password"
                    value="password"
                />
            @endif

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-2">
                    <input
                        type="checkbox"
                        name="remember"
                        class="checkbox checkbox-primary"
                        {{ old('remember') ? 'checked' : '' }}
                    />
                    <span class="label-text">{{ __('ui.auth.login.remember_me') }}</span>
                </label>
            </div>

            <x-ui.button
                type="submit"
                variant="primary"
                class="w-full"
                data-test="login-button"
            >
                {{ __('ui.auth.login.submit') }}
            </x-ui.button>
        </x-ui.form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-base-content/70">
                <span>{{ __('ui.auth.login.no_account') }}</span>
                <a
                    href="{{ route('register') }}"
                    wire:navigate
                    class="link link-primary"
                >
                    {{ __('ui.auth.login.sign_up') }}
                </a>
            </div>
        @endif
    </div>
</x-layouts.auth>
