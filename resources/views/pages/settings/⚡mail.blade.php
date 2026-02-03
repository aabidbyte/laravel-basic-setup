<?php

use App\Constants\Auth\Permissions;
use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Models\MailSettings;
use App\Services\Notifications\NotificationBuilder;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

new class extends BasePageComponent {
    public ?string $pageTitle = 'settings.tabs.mail';

    public ?string $pageSubtitle = 'settings.mail.description';

    protected PlaceholderType $placeholderType = PlaceholderType::FORM;

    protected int $placeholderRows = 5;

    public string $provider = 'smtp';

    public string $host = '';

    public ?int $port = 587;

    public string $username = '';

    public string $password = '';

    public string $encryption = 'tls';

    public string $fromAddress = '';

    public string $fromName = '';

    public bool $isActive = true;

    public bool $hasExistingSettings = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::CONFIGURE_MAIL_SETTINGS());

        $settings = MailSettings::getForUser(Auth::user());

        if ($settings) {
            $this->hasExistingSettings = true;
            $this->provider = $settings->provider;
            $this->host = $settings->host ?? '';
            $this->port = $settings->port;
            $this->username = $settings->username ?? '';
            $this->password = ''; // Never expose password
            $this->encryption = $settings->encryption ?? 'tls';
            $this->fromAddress = $settings->from_address ?? '';
            $this->fromName = $settings->from_name ?? '';
            $this->isActive = $settings->is_active;
        }
    }

    /**
     * Get available providers.
     */
    public function getProvidersProperty(): array
    {
        return [
            'smtp' => 'SMTP',
            'ses' => 'Amazon SES',
            'postmark' => 'Postmark',
            'resend' => 'Resend',
        ];
    }

    /**
     * Get available encryption options.
     */
    public function getEncryptionOptionsProperty(): array
    {
        return [
            'tls' => 'TLS',
            'ssl' => 'SSL',
            '' => __('common.none'),
        ];
    }

    /**
     * Save mail settings.
     */
    public function saveSettings(): void
    {
        $this->authorize(Permissions::CONFIGURE_MAIL_SETTINGS());

        $validated = $this->validate([
            'provider' => ['required', 'string', 'in:smtp,ses,postmark,resend'],
            'host' => ['required_if:provider,smtp', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:provider,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'string', 'in:tls,ssl,'],
            'fromAddress' => ['required', 'email', 'max:255'],
            'fromName' => ['required', 'string', 'max:255'],
            'isActive' => ['boolean'],
        ]);

        $user = Auth::user();
        $settings = MailSettings::getForUser($user);

        $data = [
            'provider' => $this->provider,
            'host' => $this->host ?: null,
            'port' => $this->port ?: null,
            'username' => $this->username ?: null,
            'encryption' => $this->encryption ?: null,
            'from_address' => $this->fromAddress,
            'from_name' => $this->fromName,
            'is_active' => $this->isActive,
        ];

        // Only update password if provided
        if (!empty($this->password)) {
            $data['password'] = $this->password;
        }

        if ($settings) {
            $settings->fill($data)->save();
        } else {
            $user->mailSettings()->create(
                \array_merge($data, [
                    'settable_type' => get_class($user),
                    'settable_id' => $user->id,
                ]),
            );
            $this->hasExistingSettings = true;
        }

        $this->reset('password');

        NotificationBuilder::make()->title('settings.mail.save_success')->success()->send();
    }

    /**
     * Test the mail connection.
     */
    public function testConnection(): void
    {
        $this->authorize(Permissions::CONFIGURE_MAIL_SETTINGS());

        try {
            // Create a temporary mailer config
            $config = [
                'transport' => $this->provider,
                'host' => $this->host,
                'port' => $this->port,
                'username' => $this->username,
                'password' => $this->password ?: MailSettings::getForUser(Auth::user())?->password ?? '',
                'encryption' => $this->encryption ?: null,
            ];

            // For SMTP, try to connect
            if ($this->provider === 'smtp' && !empty($this->host)) {
                $transport = new EsmtpTransport($this->host, $this->port ?? 587);

                if (!empty($this->username)) {
                    $transport->setUsername($this->username);
                    $transport->setPassword($config['password']);
                }

                // This will throw an exception if connection fails
                $transport->start();
                $transport->stop();
            }

            NotificationBuilder::make()->title('settings.mail.test_success')->success()->send();
        } catch (Exception $e) {
            NotificationBuilder::make()->title('settings.mail.test_failed')->subtitle($e->getMessage())->error()->send();
        }
    }

    /**
     * Delete mail settings.
     */
    public function deleteSettings(): void
    {
        $this->authorize(Permissions::CONFIGURE_MAIL_SETTINGS());

        $settings = MailSettings::getForUser(Auth::user());

        if ($settings) {
            $settings->delete();
            $this->hasExistingSettings = false;
            $this->reset(['provider', 'host', 'port', 'username', 'password', 'encryption', 'fromAddress', 'fromName', 'isActive']);
            $this->provider = 'smtp';
            $this->port = 587;
            $this->encryption = 'tls';
            $this->isActive = true;

            NotificationBuilder::make()->title('settings.mail.delete_success')->info()->send();
        }
    }
}; ?>

<x-layouts.page>
    <section class="max-w-4xl space-y-6"
             @confirm-delete-mail-settings.window="$wire.deleteSettings()">
        <x-settings.layout>
            <x-ui.form wire:submit="saveSettings"
                       class="w-full space-y-6">
                {{-- Provider Selection --}}
                <x-ui.select label="{{ __('settings.mail.provider_label') }}"
                             name="provider"
                             wire:model.live="provider"
                             :options="$this->providers"
                             :prepend-empty="false" />

                {{-- SMTP Settings (only shown for SMTP provider) --}}
                @if ($provider === 'smtp')
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-ui.input type="text"
                                    wire:model="host"
                                    name="host"
                                    :label="__('settings.mail.host_label')"
                                    placeholder="smtp.example.com"
                                    required></x-ui.input>

                        <x-ui.input type="number"
                                    wire:model="port"
                                    name="port"
                                    :label="__('settings.mail.port_label')"
                                    placeholder="587"
                                    min="1"
                                    max="65535"
                                    required></x-ui.input>

                        <x-ui.input type="text"
                                    wire:model="username"
                                    name="username"
                                    :label="__('settings.mail.username_label')"
                                    autocomplete="username"></x-ui.input>

                        <x-ui.password wire:model="password"
                                       name="password"
                                       :label="__('settings.mail.password_label')"
                                       :placeholder="$hasExistingSettings ? __('settings.mail.password_placeholder') : ''"
                                       autocomplete="new-password"></x-ui.password>
                    </div>

                    <x-ui.select label="{{ __('settings.mail.encryption_label') }}"
                                 name="encryption"
                                 wire:model="encryption"
                                 :options="$this->encryptionOptions" />
                @endif

                <div class="divider"></div>

                {{-- From Address Settings --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input type="email"
                                wire:model="fromAddress"
                                name="fromAddress"
                                :label="__('settings.mail.from_address_label')"
                                placeholder="noreply@example.com"
                                required></x-ui.input>

                    <x-ui.input type="text"
                                wire:model="fromName"
                                name="fromName"
                                :label="__('settings.mail.from_name_label')"
                                :placeholder="config('app.name')"
                                required></x-ui.input>
                </div>

                {{-- Active Toggle --}}
                <x-ui.toggle wire:model="isActive"
                             :label="__('settings.mail.active_label')"
                             :description="__('settings.mail.active_help')"
                             color="primary" />

                {{-- Actions --}}
                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <x-ui.button type="submit"
                                 color="primary"
                                 data-test="save-mail-settings-button">
                        {{ __('actions.save') }}
                    </x-ui.button>

                    <x-ui.button type="button"
                                 wire:click="testConnection"
                                 color="secondary">
                        <x-ui.icon name="paper-airplane"
                                   class="h-4 w-4"></x-ui.icon>
                        {{ __('settings.mail.test_button') }}
                    </x-ui.button>

                    @if ($hasExistingSettings)
                        <x-ui.button type="button"
                                     @click="$dispatch('confirm-modal', {
                                     title: '{{ __('actions.delete') }}',
                                     message: '{{ __('settings.mail.delete_confirm') }}',
                                     confirmColor: 'error',
                                     confirmEvent: 'confirm-delete-mail-settings'
                                 })"
                                     color="error"
                                     variant="outline">
                            {{ __('actions.delete') }}
                        </x-ui.button>
                    @endif
                </div>
            </x-ui.form>
        </x-settings.layout>
    </section>
</x-layouts.page>
