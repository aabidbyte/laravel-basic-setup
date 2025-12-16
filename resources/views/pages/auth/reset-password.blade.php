<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('ui.auth.reset_password.title')" :description="__('ui.auth.reset_password.description')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <x-ui.input type="email" name="email" :label="__('ui.auth.reset_password.email_label')" :value="request('email')" required autocomplete="email" />

            <x-ui.input type="password" name="password" :label="__('ui.auth.reset_password.password_label')" required autocomplete="new-password"
                :placeholder="__('ui.auth.reset_password.password_placeholder')" />

            <x-ui.input type="password" name="password_confirmation" :label="__('ui.auth.reset_password.confirm_password_label')" required
                autocomplete="new-password" :placeholder="__('ui.auth.reset_password.confirm_password_placeholder')" />

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                {{ __('ui.auth.reset_password.submit') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts.auth>
