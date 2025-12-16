<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public string $name = '';

    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
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

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('ui.settings.profile.title')" :subheading="__('ui.settings.profile.description')">
        <x-ui.form wire:submit="updateProfileInformation" class="w-full">
            <x-ui.input type="text" wire:model="name" name="name" :label="__('ui.settings.profile.name_label')" required autofocus
                autocomplete="name" />

            <x-ui.input type="email" wire:model="email" name="email" :label="__('ui.settings.profile.email_label')" required autocomplete="email" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                <div class="alert alert-info mt-4">
                    <span class="text-sm">
                        {{ __('ui.settings.profile.email_unverified') }}
                        <button type="button" wire:click.prevent="resendVerificationNotification"
                            class="link link-primary">
                            {{ __('ui.settings.profile.resend_verification') }}
                        </button>
                    </span>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success mt-2">
                        <span>{{ __('ui.settings.profile.verification_sent') }}</span>
                    </div>
                @endif
            @endif

            <div class="flex items-center gap-4">
                <x-ui.button type="submit" variant="primary" class="w-full" data-test="update-profile-button">
                    {{ __('ui.actions.save') }}
                </x-ui.button>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('ui.settings.profile.save_success') }}
                </x-action-message>
            </div>
        </x-ui.form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
