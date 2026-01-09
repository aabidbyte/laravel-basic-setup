<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('authentication.login.title')"
                       :description="__('authentication.login.description')"></x-auth-header>

        <x-ui.form method="POST"
                   action="{{ route('login.store') }}"
                   class="flex flex-col">
            @csrf

            {{-- Production: Show text input; Dev: Show user select dropdown --}}
            @if (isProduction())
                <x-ui.input type="text"
                            name="identifier"
                            :label="__('authentication.login.email_label')"
                            :value="old('identifier')"
                            required
                            autofocus
                            autocomplete="username"
                            :placeholder="__('authentication.login.email_placeholder')"></x-ui.input>
                <x-ui.password name="password"
                               :label="__('authentication.login.password_label')"
                               required
                               autocomplete="current-password"
                               :placeholder="__('authentication.login.password_placeholder')">
                    @if (Route::has('password.request'))
                        <x-slot:label-append>
                            <x-ui.link href="{{ route('password.request') }}"
                                       class="label-text-alt">{{ __('authentication.login.forgot_password') }}</x-ui.link>
                        </x-slot:label-append>
                    @endif
                </x-ui.password>
            @else
                {{-- Dev mode: Hide password field and use default password --}}
                <x-ui.select name="identifier"
                             :label="__('authentication.login.email_label')"
                             :options="$users"
                             :selected="old('identifier')"
                             :placeholder="__('authentication.login.select_user')"
                             :error="$errors->first('identifier')"
                             required
                             autofocus />
                <input type="hidden"
                       name="password"
                       value="password" />
            @endif

            <x-ui.checkbox name="remember"
                           label="{{ __('authentication.login.remember_me') }}"
                           color="primary"
                           :checked="old('remember')" />

            <x-ui.button type="submit"
                         variant="primary"
                         class="w-full"
                         data-test="login-button">
                {{ __('authentication.login.submit') }}
            </x-ui.button>
        </x-ui.form>

        @if (Route::has('register'))
            <div class="text-base-content/70 text-center text-sm">
                <span>{{ __('authentication.login.no_account') }}</span>
                <x-ui.link href="{{ route('register') }}">{{ __('authentication.login.sign_up') }}</x-ui.link>
            </div>
        @endif
    </div>
</x-layouts.auth>
