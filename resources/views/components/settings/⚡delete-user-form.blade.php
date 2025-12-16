<?php

use App\Livewire\Actions\Logout;
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

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h2 class="text-xl font-bold text-base-content">{{ __('ui.settings.delete_account.title') }}</h2>
        <p class="mt-1 text-sm text-base-content/70">{{ __('ui.settings.delete_account.description') }}</p>
    </div>

    <x-ui.button type="button" variant="error" onclick="confirm_user_deletion_modal.showModal()"
        data-test="delete-user-button">
        {{ __('ui.settings.delete_account.button') }}
    </x-ui.button>

    <x-ui.modal id="confirm_user_deletion_modal" :title="__('ui.settings.delete_account.modal_title')">
        <p class="text-base-content/70 mb-6">
            {{ __('ui.settings.delete_account.modal_description') }}
        </p>
        <form id="delete-user-form" method="POST" wire:submit="deleteUser" class="space-y-4">
            <x-ui.input type="password" wire:model="password" name="password" :label="__('ui.settings.delete_account.password_label')" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="error" form="delete-user-form"
                    data-test="confirm-delete-user-button">
                    {{ __('ui.settings.delete_account.button') }}
                </x-ui.button>
            </x-slot:actions>
        </form>


    </x-ui.modal>
</section>
