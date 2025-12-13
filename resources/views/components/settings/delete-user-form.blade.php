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
        <h2 class="text-xl font-bold text-base-content">{{ __('Delete account') }}</h2>
        <p class="mt-1 text-sm text-base-content/70">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <button type="button" onclick="confirm_user_deletion_modal.showModal()" class="btn btn-error"
        data-test="delete-user-button">
        {{ __('Delete account') }}
    </button>

    <dialog id="confirm_user_deletion_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">{{ __('Are you sure you want to delete your account?') }}</h3>
            <p class="text-base-content/70 mb-6">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>
            <form method="POST" wire:submit="deleteUser" class="space-y-4">
                <x-ui.input type="password" wire:model="password" name="password" :label="__('Password')" />
                <div class="modal-action">
                    <form method="dialog">
                        <button type="button" class="btn">{{ __('Cancel') }}</button>
                    </form>
                    <button type="submit" class="btn btn-error" data-test="confirm-delete-user-button">
                        {{ __('Delete account') }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</section>
