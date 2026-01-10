<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Services\Notifications\NotificationBuilder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Symfony\Component\HttpFoundation\Response;

new class extends BasePageComponent {
    public ?string $pageTitle = 'settings.tabs.security';

    public ?string $pageSubtitle = 'settings.security.description';

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    // Two-factor authentication state
    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $user = Auth::user();

        // Handle two-factor authentication
        if (Features::enabled(Features::twoFactorAuthentication())) {
            if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm') && is_null($user->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication($user);
            }

            $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        } else {
            $this->twoFactorEnabled = false;
            $this->requiresConfirmation = false;
        }
    }

    /**
     * Get all active sessions for the current user.
     */
    #[Computed]
    public function sessions(): Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', Auth::id())
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $this->parseUserAgent($session->user_agent),
                    'last_activity' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'is_current' => $session->id === session()->getId(),
                ];
            });
    }

    /**
     * Parse user agent string into a readable format.
     */
    private function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'browser' => __('common.unknown'),
                'platform' => __('common.unknown'),
            ];
        }

        // Simple browser detection
        $browser = __('common.unknown');
        if (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edg')) {
            $browser = 'Edge';
        }

        // Simple platform detection
        $platform = __('common.unknown');
        if (str_contains($userAgent, 'Windows')) {
            $platform = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $platform = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $platform = 'Linux';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $platform = 'iOS';
        } elseif (str_contains($userAgent, 'Android')) {
            $platform = 'Android';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Revoke a specific session.
     */
    public function revokeSession(string $sessionId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        // Cannot revoke current session
        if ($sessionId === session()->getId()) {
            NotificationBuilder::make()->title('settings.security.cannot_revoke_current')->warning()->send();

            return;
        }

        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->where('id', $sessionId)->where('user_id', Auth::id())->delete();

        NotificationBuilder::make()->title('settings.security.session_revoked')->success()->send();

        unset($this->sessions);
    }

    /**
     * Revoke all other sessions.
     */
    public function revokeAllOtherSessions(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', Auth::id())
            ->where('id', '!=', session()->getId())
            ->delete();

        NotificationBuilder::make()->title('settings.security.all_sessions_revoked')->success()->send();

        unset($this->sessions);
    }

    // == Two-Factor Authentication Methods ==

    /**
     * Enable two-factor authentication for the user.
     */
    public function enableTwoFactor(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $user = Auth::user();

        $enableTwoFactorAuthentication($user);

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->dispatch('open-two-factor-setup-modal');
    }

    /**
     * Load the two-factor authentication setup data.
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
     * Show the verification step.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->resetErrorBag();

            $this->dispatch('show-verification-step');

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(Auth::user(), $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;

        NotificationBuilder::make()->title('settings.two_factor.enabled_success')->success()->send();
    }

    /**
     * Reset verification state.
     */
    #[On('reset-verification')]
    public function resetVerification(): void
    {
        $this->reset('code');

        $this->resetErrorBag();

        $this->dispatch('hide-verification-step');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(Auth::user());

        $this->twoFactorEnabled = false;

        NotificationBuilder::make()->title('settings.two_factor.disabled_success')->info()->send();
    }

    /**
     * Close the two-factor modal.
     */
    public function closeModal(): void
    {
        $this->reset('code', 'manualSetupKey', 'qrCodeSvg');

        $this->resetErrorBag();

        $this->dispatch('close-two-factor-setup-modal');

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = Auth::user()->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Get the modal configuration.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('settings.two_factor.setup.title_enabled'),
                'description' => __('settings.two_factor.setup.description_enabled'),
                'buttonText' => __('actions.close'),
            ];
        }

        return [
            'title' => __('settings.two_factor.setup.title_setup'),
            'description' => __('settings.two_factor.setup.description_setup'),
            'buttonText' => __('actions.continue'),
        ];
    }
}; ?>

<section class="w-full">
    <x-settings.layout>
        <div class="space-y-8">
            {{-- Two-Factor Authentication Section --}}
            @if (Features::enabled(Features::twoFactorAuthentication()))
                <div>
                    <h2 class="text-base-content mb-4 text-lg font-semibold">{{ __('settings.two_factor.title') }}</h2>

                    @if ($twoFactorEnabled)
                        <div class="card bg-base-200">
                            <div class="card-body">
                                <div class="flex items-center gap-3">
                                    <x-ui.badge color="success"
                                                size="lg">{{ __('settings.two_factor.enabled') }}</x-ui.badge>
                                </div>

                                <p class="text-base-content/70">
                                    {{ __('settings.two_factor.enabled_description') }}
                                </p>

                                <livewire:settings.two-factor.recovery-codes
                                                                             :$requiresConfirmation></livewire:settings.two-factor.recovery-codes>

                                <div class="card-actions mt-4">
                                    <x-ui.button type="button"
                                                 wire:click="disableTwoFactor"
                                                 variant="error">
                                        {{ __('settings.two_factor.disable_button') }}
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card bg-base-200">
                            <div class="card-body">
                                <div class="flex items-center gap-3">
                                    <x-ui.badge color="error"
                                                size="lg">{{ __('settings.two_factor.disabled') }}</x-ui.badge>
                                </div>

                                <p class="text-base-content/70">
                                    {{ __('settings.two_factor.disabled_description') }}
                                </p>

                                <div class="card-actions mt-4">
                                    <x-ui.button type="button"
                                                 wire:click="enableTwoFactor"
                                                 variant="primary">
                                        {{ __('settings.two_factor.enable_button') }}
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Two-Factor Setup Modal --}}
                <div x-data="twoFactorSetupTrigger()"
                     x-show="showModal"
                     style="display: none;">
                    <livewire:settings.two-factor.setup-modal :modal-config="$this->modalConfig"
                                                              :qr-code-svg="$qrCodeSvg"
                                                              :manual-setup-key="$manualSetupKey"
                                                              lazy />
                </div>
            @endif

            <div class="divider"></div>

            {{-- Active Sessions Section --}}
            <div>
                <h2 class="text-base-content mb-2 text-lg font-semibold">{{ __('settings.security.sessions_title') }}
                </h2>
                <p class="text-base-content/70 mb-4 text-sm">{{ __('settings.security.sessions_description') }}</p>

                @if (config('session.driver') === 'database')
                    <div class="space-y-3">
                        @forelse ($this->sessions as $session)
                            <div @class([
                                'card bg-base-200',
                                'border-2 border-primary' => $session['is_current'],
                            ])>
                                <div class="card-body px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <x-ui.icon name="computer-desktop"
                                                       class="text-base-content/70 h-6 w-6"></x-ui.icon>
                                            <div>
                                                <div class="font-medium">
                                                    {{ $session['user_agent']['browser'] }} on
                                                    {{ $session['user_agent']['platform'] }}
                                                    @if ($session['is_current'])
                                                        <x-ui.badge color="primary"
                                                                    size="sm"
                                                                    class="ml-2">{{ __('settings.security.current_session') }}</x-ui.badge>
                                                    @endif
                                                </div>
                                                <div class="text-base-content/70 text-sm">
                                                    {{ $session['ip_address'] ?? __('common.unknown') }} •
                                                    {{ $session['last_activity'] }}
                                                </div>
                                            </div>
                                        </div>

                                        @if (!$session['is_current'])
                                            <x-ui.button type="button"
                                                         wire:click="revokeSession('{{ $session['id'] }}')"
                                                         wire:confirm="{{ __('settings.security.revoke_confirm') }}"
                                                         variant="ghost"
                                                         size="sm">
                                                <x-ui.icon name="x-mark"
                                                           class="h-4 w-4"></x-ui.icon>
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert">
                                <x-ui.icon name="information-circle"
                                           class="h-5 w-5"></x-ui.icon>
                                <span>{{ __('settings.security.no_sessions') }}</span>
                            </div>
                        @endforelse

                        @if ($this->sessions->count() > 1)
                            <x-ui.button type="button"
                                         wire:click="revokeAllOtherSessions"
                                         wire:confirm="{{ __('settings.security.revoke_all_confirm') }}"
                                         variant="error"
                                         class="mt-4">
                                {{ __('settings.security.revoke_all_button') }}
                            </x-ui.button>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning">
                        <x-ui.icon name="exclamation-triangle"
                                   class="h-5 w-5"></x-ui.icon>
                        <span>{{ __('settings.security.sessions_unavailable') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </x-settings.layout>
</section>
