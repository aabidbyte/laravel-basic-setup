<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('authentication.forgot_password.title')"
                       :description="__('authentication.forgot_password.description')"></x-auth-header>

        <form method="POST"
              action="{{ route('password.email') }}"
              class="flex flex-col gap-6">
            @csrf

            <x-ui.input type="text"
                        name="identifier"
                        :label="__('authentication.forgot_password.identifier_label')"
                        required
                        autofocus
                        :placeholder="__('authentication.login.email_placeholder')"></x-ui.input>

            <x-ui.button type="submit"
                         color="primary"
                         class="w-full"
                         data-test="email-password-reset-link-button">
                {{ __('authentication.forgot_password.submit') }}
            </x-ui.button>
        </form>

        <div class="text-base-content/70 text-center text-sm">
            <span>{{ __('authentication.forgot_password.back_to_login') }}</span>
            <x-ui.link href="{{ route('login') }}">{{ __('authentication.forgot_password.log_in') }}</x-ui.link>
        </div>
    </div>
</x-layouts.auth>
