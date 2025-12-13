<x-layouts.auth>
    <div class="mt-4 flex flex-col gap-6">
        <div class="alert">
            <span class="text-base-content/70">
                {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
            </span>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                <span>
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </span>
            </div>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-ui.button type="submit" variant="primary" class="w-full">
                    {{ __('Resend verification email') }}
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" class="text-sm" data-test="logout-button">
                    {{ __('Log out') }}
                </x-ui.button>
            </form>
        </div>
    </div>
</x-layouts.auth>
