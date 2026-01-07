<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('authentication.confirm_password.title')"
            :description="__('authentication.confirm_password.description')"
        ></x-auth-header>

        <form
            method="POST"
            action="{{ route('password.confirm.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            <x-ui.password
                name="password"
                :label="__('authentication.confirm_password.password_label')"
                required
                autocomplete="current-password"
                :placeholder="__('authentication.confirm_password.password_placeholder')"
            ></x-ui.password>

            <x-ui.button
                type="submit"
                variant="primary"
                class="w-full"
                data-test="confirm-password-button"
            >
                {{ __('actions.confirm') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts.auth>
