<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
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

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <x-ui.form method="POST" wire:submit="updatePassword">
            <x-ui.input type="password" wire:model="current_password" name="current_password" :label="__('Current password')" required
                autocomplete="current-password" />

            <x-ui.input type="password" wire:model="password" name="password" :label="__('New password')" required
                autocomplete="new-password" />

            <x-ui.input type="password" wire:model="password_confirmation" name="password_confirmation"
                :label="__('Confirm Password')" required autocomplete="new-password" />

            <div class="flex items-center gap-4">
                <x-ui.button type="submit" variant="primary" class="w-full" data-test="update-password-button">
                    {{ __('Save') }}
                </x-ui.button>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </x-ui.form>
    </x-settings.layout>
</section>
