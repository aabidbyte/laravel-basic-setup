<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('authentication.register.title')"
                       :description="__('authentication.register.description')"></x-auth-header>

        <x-ui.form method="POST"
                   action="{{ route('register.store') }}"
                   class="flex flex-col">

            <x-ui.input type="text"
                        name="name"
                        :label="__('authentication.register.name_label')"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                        :placeholder="__('authentication.register.name_placeholder')"></x-ui.input>

            <x-ui.input type="email"
                        name="email"
                        :label="__('authentication.register.email_label')"
                        :value="old('email')"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"></x-ui.input>

            <x-ui.password name="password"
                           :label="__('authentication.register.password_label')"
                           required
                           autocomplete="new-password"
                           :placeholder="__('authentication.register.password_placeholder')"
                           with-generation
                           with-strength-meter></x-ui.password>

            <x-ui.password name="password_confirmation"
                           :label="__('authentication.register.confirm_password_label')"
                           required
                           autocomplete="new-password"
                           :placeholder="__('authentication.register.confirm_password_placeholder')"></x-ui.password>

            <x-ui.button type="submit"
                         color="primary"
                         class="w-full"
                         data-test="register-user-button">
                {{ __('authentication.register.submit') }}
            </x-ui.button>
        </x-ui.form>

        <div class="text-base-content/70 text-center text-sm">
            <span>{{ __('authentication.register.has_account') }}</span>
            <x-ui.link href="{{ route('login') }}">{{ __('authentication.register.log_in') }}</x-ui.link>
        </div>
    </div>
</x-layouts.auth>
