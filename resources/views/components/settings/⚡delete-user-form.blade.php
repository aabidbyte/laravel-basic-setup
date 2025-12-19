<?php

use App\Livewire\Actions\Logout;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();
        tap($user, $logout(...))->delete();

        NotificationBuilder::make()->title(__('ui.settings.delete_account.success'))->info()->send();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h2 class="text-xl font-bold text-base-content">{{ __('ui.settings.delete_account.title') }}</h2>
        <p class="mt-1 text-sm text-base-content/70">{{ __('ui.settings.delete_account.description') }}</p>
    </div>

    <x-ui.button type="button" variant="error" onclick="document.getElementById('delete_account_modal')?.showModal()"
        data-test="delete-user-button">
        {{ __('ui.settings.delete_account.button') }}
    </x-ui.button>

    <x-ui.confirm-modal id="delete_account_modal" :show-icon="true">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <x-ui.icon name="exclamation-triangle" class="h-8 w-8 text-error" />
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold">{{ __('ui.settings.delete_account.modal_title') }}</h3>
                <p class="text-base-content/70 mb-6 mt-2">
                    {{ __('ui.settings.delete_account.modal_description') }}
                </p>
                <form id="delete-user-form" method="POST" wire:submit="deleteUser" class="space-y-4">
                    <x-ui.password wire:model="password" name="password" :label="__('ui.settings.delete_account.password_label')" />

                    <div class="flex justify-end gap-2 mt-4">
                        <x-ui.button type="submit" variant="error" form="delete-user-form"
                            data-test="confirm-delete-user-button">
                            {{ __('ui.settings.delete_account.button') }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>

        <x-slot:actions>
            <form method="dialog">
                <x-ui.button type="button" variant="ghost">
                    {{ __('ui.actions.cancel') }}
                </x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.confirm-modal>
</section>
