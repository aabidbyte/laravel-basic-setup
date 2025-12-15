<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-ui.form method="POST" action="{{ route('register.store') }}" class="flex flex-col">

            <x-ui.input type="text" name="name" :label="__('Name')" :value="old('name')" required autofocus
                autocomplete="name" :placeholder="__('Full name')" />

            <x-ui.input type="email" name="email" :label="__('Email address')" :value="old('email')" required autocomplete="email"
                placeholder="email@example.com" />

            <x-ui.input type="password" name="password" :label="__('Password')" required autocomplete="new-password"
                :placeholder="__('Password')" />

            <x-ui.input type="password" name="password_confirmation" :label="__('Confirm password')" required
                autocomplete="new-password" :placeholder="__('Confirm password')" />

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </x-ui.button>
        </x-ui.form>

        <div class="text-center text-sm text-base-content/70">
            <span>{{ __('Already have an account?') }}</span>
            <a href="{{ route('login') }}" wire:navigate class="link link-primary">
                {{ __('Log in') }}
            </a>
        </div>
    </div>
</x-layouts.auth>
