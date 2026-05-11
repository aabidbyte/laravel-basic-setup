@props([
    'title' => null,
    'description' => __('common.type_confirm.description'),
    'itemLabel' => '',
    'confirmButtonText' => __('actions.force_delete'),
    'confirmEvent' => null,
    'confirmData' => null,
    'onConfirm' => null,
    'onCancel' => null,
    'color' => 'danger',
])

<div x-data="typeConfirmDialogBody()"
     data-item-label="{{ $itemLabel }}"
     data-confirm-event="{{ $confirmEvent }}"
     data-confirm-data='{{ \json_encode($confirmData) }}'
     class="space-y-4">
    <div class="space-y-4 px-6 py-4">
        {{-- Description --}}
        <p class="text-base-content/70">
            {{ $description }}
        </p>

        {{-- Item name to confirm --}}
        <div class="bg-error/10 border-error/20 rounded-lg border p-3 text-center">
            <span class="text-error font-mono text-lg font-bold"
                  x-text="itemLabel"></span>
        </div>

        {{-- Confirmation input --}}
        <x-ui.input type="text"
                    x-model="confirmText"
                    @keyup.enter="confirm()"
                    x-bind:class="{ 'input-error': confirmText.length > 0 && !isConfirmValid }"
                    placeholder="{{ __('common.type_confirm.placeholder') }}"
                    :label="__('common.type_confirm.type_label')"
                    autofocus />
    </div>

    <div class="bg-base-200/50 border-base-content/5 flex justify-end gap-2 border-t px-6 py-4">
        <x-ui.button type="button"
                     variant="ghost"
                     @click="{{ $onCancel ?? 'closeModal()' }}">
            {{ __('actions.cancel') }}
        </x-ui.button>
        <x-ui.button type="button"
                     color="error"
                     @click="confirm()"
                     x-bind:disabled="!isConfirmValid">
            {{ $confirmButtonText }}
        </x-ui.button>
    </div>
</div>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('typeConfirmDialogBody', () => ({
                    itemLabel: '',
                    confirmText: '',
                    confirmEvent: null,
                    confirmData: null,

                    init() {
                        this.itemLabel = this.$el.getAttribute('data-item-label') || '';
                        this.confirmEvent = this.$el.getAttribute('data-confirm-event') || null;
                        const rawData = this.$el.getAttribute('data-confirm-data');
                        if (rawData) {
                            try {
                                this.confirmData = JSON.parse(rawData);
                            } catch (e) {
                                console.error('Failed to parse confirm data', e);
                            }
                        }
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
                            if (this.confirmEvent) {
                                window.Livewire.dispatch(this.confirmEvent, this.confirmData);
                            }
                            window.Livewire.dispatch('datatable-close-modal');
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
