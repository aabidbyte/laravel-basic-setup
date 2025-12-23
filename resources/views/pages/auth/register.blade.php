<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('ui.auth.register.title')" :description="__('ui.auth.register.description')"></x-auth-header>

        <x-auth-session-status class="text-center" :status="session('status')"></x-auth-session-status>

        <x-ui.form method="POST" action="{{ route('register.store') }}" class="flex flex-col">

            <x-ui.input type="text" name="name" :label="__('ui.auth.register.name_label')" :value="old('name')" required autofocus
                autocomplete="name" :placeholder="__('ui.auth.register.name_placeholder')"></x-ui.input>

            <x-ui.input type="email" name="email" :label="__('ui.auth.register.email_label')" :value="old('email')" required autocomplete="email"
                placeholder="email@example.com"></x-ui.input>

            <x-ui.password name="password" :label="__('ui.auth.register.password_label')" required autocomplete="new-password" :placeholder="__('ui.auth.register.password_placeholder')"></x-ui.password>

            <x-ui.password name="password_confirmation" :label="__('ui.auth.register.confirm_password_label')" required autocomplete="new-password"
                :placeholder="__('ui.auth.register.confirm_password_placeholder')"></x-ui.password>

            <x-ui.button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('ui.auth.register.submit') }}
            </x-ui.button>
        </x-ui.form>

        <div class="text-center text-sm text-base-content/70">
            <span>{{ __('ui.auth.register.has_account') }}</span>
            <a href="{{ route('login') }}" wire:navigate class="link link-primary">
                {{ __('ui.auth.register.log_in') }}
            </a>
        </div>
    </div>
</x-layouts.auth>
