<?php

use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

new class extends BasePageComponent {
    public ?string $pageTitle = 'settings.tabs.account';

    public ?string $pageSubtitle = 'settings.account.description';

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 5;

    // Profile fields
    public string $name = '';

    public string $email = '';

    // Password fields
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        NotificationBuilder::make()->title('settings.profile.save_success')->success()->persist()->send();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        NotificationBuilder::make()->title('settings.profile.verification_sent')->info()->send();
    }

    /**
     * Update the password.
     */
    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        NotificationBuilder::make()->title('settings.password.save_success')->success()->send();

        $this->dispatch('password-updated');
    }
}; ?>

<x-layouts.page>
    <x-settings.layout>
        <div class="space-y-8">
            {{-- Profile Section --}}
            <div>
                <h2 class="text-base-content mb-4 text-lg font-semibold">{{ __('settings.profile.title') }}</h2>
                <x-ui.form wire:submit="updateProfileInformation"
                           class="w-full">
                    <x-ui.input type="text"
                                wire:model="name"
                                name="name"
                                :label="__('settings.profile.name_label')"
                                required
                                autofocus
                                autocomplete="name"></x-ui.input>

                    <x-ui.input type="email"
                                wire:model="email"
                                name="email"
                                :label="__('settings.profile.email_label')"
                                required
                                autocomplete="email"></x-ui.input>

                    @if (Auth::user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !Auth::user()->hasVerifiedEmail())
                        <div class="alert alert-info mt-4">
                            <span class="text-sm">
                                {{ __('settings.profile.email_unverified') }}
                                <x-ui.button type="button"
                                             wire:click.prevent="resendVerificationNotification"
                                             variant="link"
                                             color="primary"
                                             size="sm">{{ __('settings.profile.resend_verification') }}</x-ui.button>
                            </span>
                        </div>
                    @endif

                    <div class="mt-4 flex items-center gap-4">
                        <x-ui.button type="submit"
                                     color="primary"
                                     data-test="update-profile-button">
                            {{ __('actions.save') }}
                        </x-ui.button>
                    </div>
                </x-ui.form>
            </div>

            <div class="divider"></div>

            {{-- Password Section --}}
            <div>
                <h2 class="text-base-content mb-4 text-lg font-semibold">{{ __('settings.password.title') }}</h2>
                <x-ui.form wire:submit="updatePassword"
                           class="w-full">
                    <x-ui.password wire:model="current_password"
                                   name="current_password"
                                   :label="__('settings.password.current_password_label')"
                                   required
                                   autocomplete="current-password"></x-ui.password>

                    <x-ui.password wire:model="password"
                                   name="password"
                                   :label="__('settings.password.new_password_label')"
                                   required
                                   autocomplete="new-password"
                                   with-generation
                                   with-strength-meter></x-ui.password>

                    <x-ui.password wire:model="password_confirmation"
                                   name="password_confirmation"
                                   :label="__('settings.password.confirm_password_label')"
                                   required
                                   autocomplete="new-password"></x-ui.password>

                    <div class="mt-4 flex items-center gap-4">
                        <x-ui.button type="submit"
                                     color="primary"
                                     data-test="update-password-button">
                            {{ __('actions.save') }}
                        </x-ui.button>
                    </div>
                </x-ui.form>
            </div>
        </div>
    </x-settings.layout>
</x-layouts.page>
