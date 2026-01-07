<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('authentication.reset_password.title')"
            :description="__('authentication.reset_password.description')"
        ></x-auth-header>

        <form
            method="POST"
            action="{{ route('password.update') }}"
            class="flex flex-col gap-6"
        >
            @csrf
            <input
                type="hidden"
                name="token"
                value="{{ request()->route('token') }}"
            >

            <x-ui.input
                type="email"
                name="email"
                :label="__('authentication.reset_password.email_label')"
                :value="request('email')"
                required
                autocomplete="email"
            ></x-ui.input>

            <x-ui.password
                name="password"
                :label="__('authentication.reset_password.password_label')"
                required
                autocomplete="new-password"
                :placeholder="__('authentication.reset_password.password_placeholder')"
                with-strength-meter
            ></x-ui.password>

            <x-ui.password
                name="password_confirmation"
                :label="__('authentication.reset_password.confirm_password_label')"
                required
                autocomplete="new-password"
                :placeholder="__('authentication.reset_password.confirm_password_placeholder')"
            ></x-ui.password>

            <x-ui.button
                type="submit"
                variant="primary"
                class="w-full"
                data-test="reset-password-button"
            >
                {{ __('authentication.reset_password.submit') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts.auth>
