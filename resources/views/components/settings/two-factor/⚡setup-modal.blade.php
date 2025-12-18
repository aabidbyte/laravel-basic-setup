<?php

use App\Services\Notifications\NotificationBuilder;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    #[Locked]
    public bool $showVerificationStep = false;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    #[Locked]
    public array $modalConfig = [];

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    public function mount(array $modalConfig = [], bool $showVerificationStep = false, string $qrCodeSvg = '', string $manualSetupKey = ''): void
    {
        $this->modalConfig = $modalConfig;
        $this->showVerificationStep = $showVerificationStep;
        $this->qrCodeSvg = $qrCodeSvg;
        $this->manualSetupKey = $manualSetupKey;
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(Auth::user(), $this->code);

        NotificationBuilder::make()->title(__('ui.settings.two_factor.enabled_success'))->success()->send();

        $this->dispatch('two-factor-confirmed');

        $this->dispatch('close-modal', 'two-factor-setup');
        $this->dispatch('$parent.closeModal');
    }

    public function resetVerification(): void
    {
        $this->reset('code');
        $this->resetErrorBag();
        $this->dispatch('reset-verification');
    }
}; ?>

<x-ui.modal id="two-factor-setup" :title="$modalConfig['title'] ?? ''" max-width="md" :auto-open="true" x-data="{ modalId: 'two-factor-setup' }"
    @close-modal.window="if ($event.detail === modalId) { const modal = document.getElementById(modalId); if (modal && modal.open) { modal.close(); } $wire.$parent.closeModal(); }">
    <div class="flex flex-col items-center space-y-4 mb-6">
        <div class="avatar placeholder">
            <div class="w-24 rounded-full bg-base-200">
                <svg class="h-12 w-12 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2.01M5 16h2.01M12 8h.01M12 16h.01M16 8h.01M16 16h.01M20 8h.01M20 16h.01" />
                </svg>
            </div>
        </div>
    </div>

    @if ($showVerificationStep)
        <div class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">{{ __('ui.settings.two_factor.setup.otp_label') }}</span>
                </label>
                <input type="text" wire:model="code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                    class="input input-bordered w-full text-center text-2xl tracking-widest @error('code') input-error @enderror"
                    placeholder="000000" />
                @error('code')
                    <div class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="flex gap-2">
                <x-ui.button type="button" variant="outline" wire:click="resetVerification" class="flex-1">
                    {{ __('ui.actions.back') }}
                </x-ui.button>
                <x-ui.button type="button" variant="primary" wire:click="confirmTwoFactor"
                    x-bind:disabled="!$wire.code || $wire.code.length < 6" class="flex-1">
                    {{ __('ui.actions.confirm') }}
                </x-ui.button>
            </div>
        </div>
    @else
        <div class="flex justify-center mb-4">
            <div class="relative w-64 overflow-hidden border border-base-300 rounded-lg aspect-square bg-base-200">
            @empty($qrCodeSvg)
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="loading loading-spinner loading-lg"></span>
                </div>
            @else
                <div class="flex items-center justify-center h-full p-4">
                    <div class="bg-base-100 p-3 rounded">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>
            @endempty
        </div>
    </div>

    <div class="divider">{{ __('ui.settings.two_factor.setup.manual_code_label') }}</div>

    <div class="join w-full">
        <input type="text" readonly value="{{ $manualSetupKey }}"
            class="input input-bordered join-item flex-1" />
        <x-ui.button type="button" variant="ghost" x-data="{ copied: false }"
            @click="
                        navigator.clipboard.writeText('{{ $manualSetupKey }}');
                        copied = true;
                        setTimeout(() => copied = false, 1500);
                    "
            class="join-item">
            <svg x-show="!copied" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <svg x-show="copied" class="h-5 w-5 text-success" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </x-ui.button>
    </div>
@endif

<x-slot:actions>
    <x-ui.button type="button" variant="primary" wire:click="$parent.showVerificationIfNecessary">
        {{ $modalConfig['buttonText'] ?? __('ui.actions.continue') }}
    </x-ui.button>
</x-slot:actions>
</x-ui.modal>
