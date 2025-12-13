<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <x-ui.input type="email" name="email" :label="__('Email')" :value="request('email')" required autocomplete="email" />

            <x-ui.input type="password" name="password" :label="__('Password')" required autocomplete="new-password"
                :placeholder="__('Password')" />

            <x-ui.input type="password" name="password_confirmation" :label="__('Confirm password')" required
                autocomplete="new-password" :placeholder="__('Confirm password')" />

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                {{ __('Reset password') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts.auth>
