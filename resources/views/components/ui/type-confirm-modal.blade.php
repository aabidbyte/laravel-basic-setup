{{--
    Type Confirm Modal Component

    A modal that requires typing an item name to confirm dangerous actions.

    Props:
    - title: Modal title (default: translated 'confirm_action')
    - description: Modal description
    - confirmLabel: Label user must type (from Alpine state 'itemLabel')
    - confirmButtonText: Text for confirm button
    - variant: Modal variant (default: 'danger')
--}}
@props([
    'title' => __('common.type_confirm.title'),
    'description' => __('common.type_confirm.description'),
    'confirmButtonText' => __('actions.force_delete'),
    'color' => 'danger',
])

<x-ui.base-modal open-state="isOpen"
                 :title="$title"
                 :color="$color"
                 transition="scale-up">
    <div class="space-y-4">
        {{-- Description --}}
        <p class="text-base-content/70">
            {{ $description }}
        </p>

        {{-- Item name to confirm --}}
        <div class="bg-error/10 rounded-lg p-3 text-center">
            <span class="text-error font-mono text-lg font-bold"
                  x-text="itemLabel"></span>
        </div>

        {{-- Confirmation input --}}
        <div class="form-control">
            <x-ui.label :text="__('common.type_confirm.type_label')"></x-ui.label>
            <input type="text"
                   x-model="confirmText"
                   @keyup.enter="confirm()"
                   class="input input-bordered w-full"
                   :class="{ 'input-error': confirmText.length > 0 && !isConfirmValid }"
                   placeholder="{{ __('common.type_confirm.placeholder') }}"
                   autofocus />
        </div>
    </div>

    <x-slot:actions>
        <x-ui.button type="button"
                     variant="ghost"
                     @click="closeModal()">
            {{ __('actions.cancel') }}
        </x-ui.button>
        <x-ui.button type="button"
                     color="error"
                     @click="confirm()"
                     :disabled="false"
                     x-bind:disabled="!isConfirmValid">
            {{ $confirmButtonText }}
        </x-ui.button>
    </x-slot:actions>
</x-ui.base-modal>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('typeConfirmModal', (config = {}) => ({
                    isOpen: false,
                    itemLabel: config.itemLabel || '',
                    confirmText: '',
                    onConfirm: config.onConfirm || (() => {}),

                    /**
                     * Open the confirmation modal
                     */
                    openModal() {
                        this.confirmText = '';
                        this.isOpen = true;
                    },

                    /**
                     * Close the modal
                     */
                    closeModal() {
                        this.isOpen = false;
                        this.confirmText = '';
                    },

                    /**
                     * Check if the typed text matches the item label
                     */
                    get isConfirmValid() {
                        return this.confirmText.trim() === this.itemLabel.trim();
                    },

                    /**
                     * Handle the confirm action
                     */
                    confirm() {
                        if (this.isConfirmValid) {
                            this.onConfirm();
                            this.closeModal();
                        }
                    },
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
