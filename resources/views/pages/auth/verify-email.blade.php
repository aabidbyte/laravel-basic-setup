<x-layouts.auth>
    <div class="mt-4 flex flex-col gap-6">
        <div class="alert">
            <span class="text-base-content/70">
                {{ __('ui.auth.verify_email.message') }}
            </span>
        </div>

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-ui.button type="submit" variant="primary" class="w-full">
                    {{ __('ui.auth.verify_email.resend_button') }}
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" class="text-sm" data-test="logout-button">
                    {{ __('ui.actions.logout') }}
                </x-ui.button>
            </form>
        </div>
    </div>
</x-layouts.auth>
