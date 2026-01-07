<?php

use App\Services\Notifications\NotificationBuilder;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use App\Livewire\Bases\LivewireBaseComponent;
use Illuminate\Support\Facades\Auth;

new class extends LivewireBaseComponent {
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(Auth::user());

        $this->loadRecoveryCodes();

        NotificationBuilder::make()->title('settings.two_factor.recovery.regenerated')->success()->send();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                NotificationBuilder::make()->title('settings.two_factor.recovery.load_error')->error()->send();

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div
    class="card bg-base-200"
    wire:cloak
    x-data="{ showRecoveryCodes: false }"
>
    <div class="card-body">
        <div class="flex items-center gap-2 mb-2">
            <svg
                class="h-5 w-5 text-base-content/70"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                />
            </svg>
            <h3 class="text-lg font-bold text-base-content">{{ __('settings.two_factor.recovery.title') }}</h3>
        </div>
        <p class="text-sm text-base-content/70 mb-4">
            {{ __('settings.two_factor.recovery.description') }}
        </p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <x-ui.button
                type="button"
                x-show="!showRecoveryCodes"
                @click="showRecoveryCodes = true"
                variant="primary"
                aria-expanded="false"
                aria-controls="recovery-codes-section"
            >
                {{ __('settings.two_factor.recovery.view_button') }}
            </x-ui.button>

            <x-ui.button
                type="button"
                x-show="showRecoveryCodes"
                @click="showRecoveryCodes = false"
                variant="primary"
                aria-expanded="true"
                aria-controls="recovery-codes-section"
            >
                {{ __('settings.two_factor.recovery.hide_button') }}
            </x-ui.button>

            @if (filled($recoveryCodes))
                <x-ui.button
                    type="button"
                    x-show="showRecoveryCodes"
                    wire:click="regenerateRecoveryCodes"
                    variant="outline"
                >
                    {{ __('settings.two_factor.recovery.regenerate_button') }}
                </x-ui.button>
            @endif
        </div>

        <div
            x-show="showRecoveryCodes"
            x-transition
            id="recovery-codes-section"
            class="mt-4"
            x-bind:aria-hidden="!showRecoveryCodes"
        >

            @if (filled($recoveryCodes))
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div
                            class="font-mono text-sm space-y-1"
                            role="list"
                            aria-label="Recovery codes"
                        >
                            @foreach ($recoveryCodes as $code)
                                <div
                                    role="listitem"
                                    class="select-text"
                                    wire:loading.class="opacity-50 animate-pulse"
                                >
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <p class="text-xs text-base-content/70 mt-2">
                    {{ __('settings.two_factor.recovery.warning') }}
                </p>
            @endif
        </div>
    </div>
</div>
