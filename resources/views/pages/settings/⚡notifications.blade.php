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

<x-layouts.page>
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
                        <x-ui.toggle wire:model="emailNotifications"
                                     :label="__('settings.notifications.email_label')"
                                     :description="__('settings.notifications.email_help')"
                                     color="primary" />

                        {{-- Browser Notifications --}}
                        <x-ui.toggle wire:model="browserNotifications"
                                     :label="__('settings.notifications.browser_label')"
                                     :description="__('settings.notifications.browser_help')"
                                     color="primary" />
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Notification Types --}}
                <div>
                    <h3 class="text-base-content mb-4 text-base font-semibold">
                        {{ __('settings.notifications.types_title') }}</h3>

                    <div class="space-y-4">
                        {{-- System Notifications --}}
                        <x-ui.toggle wire:model="systemNotifications"
                                     :label="__('settings.notifications.system_label')"
                                     :description="__('settings.notifications.system_help')"
                                     color="secondary" />

                        {{-- Security Notifications --}}
                        <x-ui.toggle wire:model="securityNotifications"
                                     :label="__('settings.notifications.security_label')"
                                     :description="__('settings.notifications.security_help')"
                                     color="secondary" />

                        {{-- Team Notifications --}}
                        <x-ui.toggle wire:model="teamNotifications"
                                     :label="__('settings.notifications.team_label')"
                                     :description="__('settings.notifications.team_help')"
                                     color="secondary" />
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
</x-layouts.page>
