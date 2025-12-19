<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('ui.auth.confirm_password.title')" :description="__('ui.auth.confirm_password.description')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-ui.password name="password" :label="__('ui.auth.confirm_password.password_label')" required autocomplete="current-password" :placeholder="__('ui.auth.confirm_password.password_placeholder')" />

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="confirm-password-button">
                {{ __('ui.actions.confirm') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts.auth>
