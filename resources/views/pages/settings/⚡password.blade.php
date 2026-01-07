<?php

use App\Livewire\Bases\BasePageComponent;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

new class extends BasePageComponent {
    public ?string $pageTitle = 'pages.settings.password';

    public ?string $pageSubtitle = 'settings.password.description';

    protected string $placeholderType = 'form';

    protected int $placeholderRows = 3;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        NotificationBuilder::make()->title('settings.password.save_success')->success()->send();

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    <x-settings.layout>
        <x-ui.form
            method="POST"
            wire:submit="updatePassword"
        >
            <x-ui.password
                wire:model="current_password"
                name="current_password"
                :label="__('settings.password.current_password_label')"
                required
                autocomplete="current-password"
            ></x-ui.password>

            <x-ui.password
                wire:model="password"
                name="password"
                :label="__('settings.password.new_password_label')"
                required
                autocomplete="new-password"
                with-strength-meter
            ></x-ui.password>

            <x-ui.password
                wire:model="password_confirmation"
                name="password_confirmation"
                :label="__('settings.password.confirm_password_label')"
                required
                autocomplete="new-password"
            ></x-ui.password>

            <div class="flex items-center gap-4">
                <x-ui.button
                    type="submit"
                    variant="primary"
                    class="w-full"
                    data-test="update-password-button"
                >
                    {{ __('actions.save') }}
                </x-ui.button>

            </div>
        </x-ui.form>
    </x-settings.layout>
</section>
