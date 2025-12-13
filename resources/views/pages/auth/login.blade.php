<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-ui.input type="email" name="email" :label="__('Email address')" :value="old('email')" required autofocus
                autocomplete="email" placeholder="email@example.com" />

            <div class="form-control w-full">
                <div class="label">
                    <span class="label-text">{{ __('Password') }}</span>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate class="label-text-alt link">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
                <input type="password" name="password" id="password" required autocomplete="current-password"
                    placeholder="{{ __('Password') }}"
                    class="input input-bordered w-full @error('password') input-error @enderror" />
                @error('password')
                    <div class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" name="remember" class="checkbox checkbox-primary"
                        {{ old('remember') ? 'checked' : '' }} />
                    <span class="label-text">{{ __('Remember me') }}</span>
                </label>
            </div>

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="login-button">
                {{ __('Log in') }}
            </x-ui.button>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-base-content/70">
                <span>{{ __('Don\'t have an account?') }}</span>
                <a href="{{ route('register') }}" wire:navigate class="link link-primary">
                    {{ __('Sign up') }}
                </a>
            </div>
        @endif
    </div>
</x-layouts.auth>
