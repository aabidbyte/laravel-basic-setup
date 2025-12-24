<?php

use App\Http\Controllers\Auth\AuthController;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();

        app(AuthController::class)->performLogout();
        $user->delete();

        // Send notification after logout/deletion
        // Since no user context is available, it will default to current session
        // This uses the current session ID dynamically (new session created after invalidation)
        // The notification will be delivered to the browser even after user deletion
        NotificationBuilder::make()
            ->title(__('ui.settings.delete_account.success'))
            ->info()
            ->send();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h2 class="text-xl font-bold text-base-content">{{ __('ui.settings.delete_account.title') }}</h2>
        <p class="mt-1 text-sm text-base-content/70">{{ __('ui.settings.delete_account.description') }}</p>
    </div>

    <div x-data="{ deleteAccountModalOpen: false }">
        <x-ui.button type="button" variant="error" @click="deleteAccountModalOpen = true" data-test="delete-user-button">
            {{ __('ui.settings.delete_account.button') }}
        </x-ui.button>

        <x-ui.confirm-modal id="delete_account_modal" :show-icon="true" open-state="deleteAccountModalOpen"
            :close-on-outside-click="false" :backdrop-transition="false">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-ui.icon name="exclamation-triangle" class="h-8 w-8 text-error"></x-ui.icon>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold">{{ __('ui.settings.delete_account.modal_title') }}</h3>
                    <p class="text-base-content/70 mb-6 mt-2">
                        {{ __('ui.settings.delete_account.modal_description') }}
                    </p>
                    <form id="delete-user-form" method="POST" wire:submit="deleteUser" class="space-y-4">
                        <x-ui.password wire:model="password" name="password" :label="__('ui.settings.delete_account.password_label')"></x-ui.password>
                    </form>
                </div>
            </div>

            <x-slot:actions>
                <x-ui.button type="submit" variant="error" form="delete-user-form"
                    data-test="confirm-delete-user-button">
                    {{ __('ui.settings.delete_account.button') }}
                </x-ui.button>
                <x-ui.button type="button" variant="ghost" @click="deleteAccountModalOpen = false">
                    {{ __('ui.actions.cancel') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.confirm-modal>
    </div>
</section>
