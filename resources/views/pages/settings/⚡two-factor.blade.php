<?php

use App\Livewire\BasePageComponent;
use App\Services\Notifications\NotificationBuilder;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Symfony\Component\HttpFoundation\Response;

new class extends BasePageComponent
{
    public ?string $pageTitle = 'ui.pages.settings.two_factor';

    public ?string $pageSubtitle = 'ui.settings.two_factor.description';

    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        $user = Auth::user();

        if (Fortify::confirmsTwoFactorAuthentication() && is_null($user->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication($user);
        }

        $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $user = Auth::user();

        $enableTwoFactorAuthentication($user);

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = Auth::user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            $this->dispatch('$refresh');

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(Auth::user(), $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;

        NotificationBuilder::make()->title(__('ui.settings.two_factor.enabled_success'))->success()->send();
    }

    /**
     * Reset two-factor verification state.
     */
    #[On('reset-verification')]
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(Auth::user());

        $this->twoFactorEnabled = false;

        NotificationBuilder::make()->title(__('ui.settings.two_factor.disabled_success'))->info()->send();
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset('code', 'manualSetupKey', 'qrCodeSvg', 'showModal', 'showVerificationStep');

        $this->resetErrorBag();

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = Auth::user()->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('ui.settings.two_factor.setup.title_enabled'),
                'description' => __('ui.settings.two_factor.setup.description_enabled'),
                'buttonText' => __('ui.actions.close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('ui.settings.two_factor.setup.title_verify'),
                'description' => __('ui.settings.two_factor.setup.description_verify'),
                'buttonText' => __('ui.actions.continue'),
            ];
        }

        return [
            'title' => __('ui.settings.two_factor.setup.title_setup'),
            'description' => __('ui.settings.two_factor.setup.description_setup'),
            'buttonText' => __('ui.actions.continue'),
        ];
    }
}; ?>

<section class="w-full">
    <x-settings.layout>
        <div class="flex flex-col w-full mx-auto space-y-6" wire:cloak>
            @if ($twoFactorEnabled)
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <x-ui.badge color="success"
                                size="lg">{{ __('ui.settings.two_factor.enabled') }}</x-ui.badge>
                        </div>

                        <p class="text-base-content/70">
                            {{ __('ui.settings.two_factor.enabled_description') }}
                        </p>

                        <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />

                        <div class="card-actions">
                            <x-ui.button type="button" wire:click="disable" variant="error">
                                {{ __('ui.settings.two_factor.disable_button') }}
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            @else
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <x-ui.badge color="error"
                                size="lg">{{ __('ui.settings.two_factor.disabled') }}</x-ui.badge>
                        </div>

                        <p class="text-base-content/70">
                            {{ __('ui.settings.two_factor.disabled_description') }}
                        </p>

                        <div class="card-actions">
                            <x-ui.button type="button" wire:click="enable" variant="primary">
                                {{ __('ui.settings.two_factor.enable_button') }}
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-settings.layout>

    @if ($showModal)
        <div wire:key="setup-modal-{{ md5(serialize($this->modalConfig)) }}">
            <livewire:settings.two-factor.setup-modal :modal-config="$this->modalConfig" :show-verification-step="$showVerificationStep" :qr-code-svg="$qrCodeSvg"
                :manual-setup-key="$manualSetupKey" lazy />
        </div>
    @endif
</section>
