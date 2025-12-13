<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <x-ui.input type="email" name="email" :label="__('Email Address')" required autofocus
                placeholder="email@example.com" />

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </x-ui.button>
        </form>

        <div class="text-center text-sm text-base-content/70">
            <span>{{ __('Or, return to') }}</span>
            <a href="{{ route('login') }}" wire:navigate class="link link-primary">
                {{ __('log in') }}
            </a>
        </div>
    </div>
</x-layouts.auth>
