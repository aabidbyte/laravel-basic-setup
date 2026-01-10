<?php

use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;

new class extends BasePageComponent {
    public ?string $pageTitle = 'settings.tabs.notifications';

    public ?string $pageSubtitle = 'settings.notifications.description';

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    public bool $emailNotifications = true;

    public bool $browserNotifications = true;

    public bool $systemNotifications = true;

    public bool $securityNotifications = true;

    public bool $teamNotifications = true;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $prefs = $user->notification_preferences ?? [];

        $this->emailNotifications = $prefs['email'] ?? true;
        $this->browserNotifications = $prefs['browser'] ?? true;
        $this->systemNotifications = $prefs['types']['system'] ?? true;
        $this->securityNotifications = $prefs['types']['security'] ?? true;
        $this->teamNotifications = $prefs['types']['team'] ?? true;
    }

    /**
     * Save notification preferences.
     */
    public function savePreferences(): void
    {
        $user = Auth::user();

        $user->notification_preferences = [
            'email' => $this->emailNotifications,
            'browser' => $this->browserNotifications,
            'types' => [
                'system' => $this->systemNotifications,
                'security' => $this->securityNotifications,
                'team' => $this->teamNotifications,
            ],
        ];

        $user->save();

        NotificationBuilder::make()->title('settings.notifications.save_success')->success()->send();
    }
}; ?>

<section class="w-full">
    <x-settings.layout>
        <x-ui.form wire:submit="savePreferences"
                   class="w-full space-y-6">
            {{-- Notification Channels --}}
            <div>
                <h3 class="text-base-content mb-4 text-base font-semibold">
                    {{ __('settings.notifications.channels_title') }}</h3>

                <div class="space-y-4">
                    {{-- Email Notifications --}}
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox"
                                   wire:model="emailNotifications"
                                   class="toggle toggle-primary" />
                            <div>
                                <span
                                      class="label-text font-medium">{{ __('settings.notifications.email_label') }}</span>
                                <p class="text-base-content/70 text-sm">{{ __('settings.notifications.email_help') }}
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Browser Notifications --}}
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox"
                                   wire:model="browserNotifications"
                                   class="toggle toggle-primary" />
                            <div>
                                <span
                                      class="label-text font-medium">{{ __('settings.notifications.browser_label') }}</span>
                                <p class="text-base-content/70 text-sm">{{ __('settings.notifications.browser_help') }}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            {{-- Notification Types --}}
            <div>
                <h3 class="text-base-content mb-4 text-base font-semibold">
                    {{ __('settings.notifications.types_title') }}</h3>

                <div class="space-y-4">
                    {{-- System Notifications --}}
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox"
                                   wire:model="systemNotifications"
                                   class="toggle toggle-secondary" />
                            <div>
                                <span
                                      class="label-text font-medium">{{ __('settings.notifications.system_label') }}</span>
                                <p class="text-base-content/70 text-sm">{{ __('settings.notifications.system_help') }}
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Security Notifications --}}
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox"
                                   wire:model="securityNotifications"
                                   class="toggle toggle-secondary" />
                            <div>
                                <span
                                      class="label-text font-medium">{{ __('settings.notifications.security_label') }}</span>
                                <p class="text-base-content/70 text-sm">{{ __('settings.notifications.security_help') }}
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Team Notifications --}}
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox"
                                   wire:model="teamNotifications"
                                   class="toggle toggle-secondary" />
                            <div>
                                <span
                                      class="label-text font-medium">{{ __('settings.notifications.team_label') }}</span>
                                <p class="text-base-content/70 text-sm">{{ __('settings.notifications.team_help') }}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <x-ui.button type="submit"
                             color="primary"
                             data-test="save-notifications-button">
                    {{ __('actions.save') }}
                </x-ui.button>
            </div>
        </x-ui.form>
    </x-settings.layout>
</section>
