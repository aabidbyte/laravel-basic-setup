<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('ui.auth.forgot_password.title')"
            :description="__('ui.auth.forgot_password.description')"
        ></x-auth-header>

        <form
            method="POST"
            action="{{ route('password.email') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            <x-ui.input
                type="email"
                name="email"
                :label="__('ui.auth.forgot_password.email_label')"
                required
                autofocus
                placeholder="email@example.com"
            ></x-ui.input>

            <x-ui.button
                type="submit"
                variant="primary"
                class="w-full"
                data-test="email-password-reset-link-button"
            >
                {{ __('ui.auth.forgot_password.submit') }}
            </x-ui.button>
        </form>

        <div class="text-center text-sm text-base-content/70">
            <span>{{ __('ui.auth.forgot_password.back_to_login') }}</span>
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="link link-primary"
            >
                {{ __('ui.auth.forgot_password.log_in') }}
            </a>
        </div>
    </div>
</x-layouts.auth>
