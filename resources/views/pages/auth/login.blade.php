<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('ui.auth.login.title')" :description="__('ui.auth.login.description')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-ui.form method="POST" action="{{ route('login.store') }}" class="flex flex-col">
            @csrf

            <x-ui.input type="email" name="email" :label="__('ui.auth.login.email_label')" :value="old('email')" required autofocus
                autocomplete="email" placeholder="email@example.com" />

            <x-ui.password name="password" :label="__('ui.auth.login.password_label')" required autocomplete="current-password" :placeholder="__('ui.auth.login.password_placeholder')">
                @if (Route::has('password.request'))
                    <x-slot:label-append>
                        <a href="{{ route('password.request') }}" wire:navigate class="label-text-alt link">
                            {{ __('ui.auth.login.forgot_password') }}
                        </a>
                    </x-slot:label-append>
                @endif
            </x-ui.password>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" name="remember" class="checkbox checkbox-primary"
                        {{ old('remember') ? 'checked' : '' }} />
                    <span class="label-text">{{ __('ui.auth.login.remember_me') }}</span>
                </label>
            </div>

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="login-button">
                {{ __('ui.auth.login.submit') }}
            </x-ui.button>
        </x-ui.form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-base-content/70">
                <span>{{ __('ui.auth.login.no_account') }}</span>
                <a href="{{ route('register') }}" wire:navigate class="link link-primary">
                    {{ __('ui.auth.login.sign_up') }}
                </a>
            </div>
        @endif
    </div>
</x-layouts.auth>
