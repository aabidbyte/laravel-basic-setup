{{-- Confirm Modal Component --}}
@php
    $modalStateId = $openState ?? 'confirmModalIsOpen_' . str_replace('-', '_', $id);
    $confirmModalConfig = \json_encode([
        'modalId' => $id,
        'title' => __('modals.confirm.title'),
        'message' => __('modals.confirm.message'),
        'confirmLabel' => __('actions.confirm'),
        'cancelLabel' => __('actions.cancel'),
    ], JSON_HEX_APOS);
@endphp

<div x-data="confirmModalData('{{ $confirmModalConfig }}')"
     x-show="isOpen"
     @confirm-modal.window="handleConfirmModal($event)"
     @confirm-modal-execute.window="executeConfirm()"
     @confirm-modal-cancel.window="closeModal()"
     x-cloak>

    <x-ui.base-modal :id="$id"
                     open-state="isOpen"
                     :use-parent-state="true"
                     max-width="{{ $maxWidth }}"
                     :placement="$placement"
                     :show-close-button="false"
                     :show-footer="true"
                     :close-on-outside-click="$closeOnOutsideClick"
                     :close-on-escape="$closeOnEscape"
                     :backdrop-transition="$backdropTransition">

        {{-- Body --}}
        @if ($slot->isEmpty())
            <x-ui.confirm-dialog-body alpine-title="title"
                                      alpine-message="message"
                                      alpine-confirm-label="confirmLabel || '{{ __('actions.confirm') }}'"
                                      alpine-cancel-label="cancelLabel || '{{ __('actions.cancel') }}'"
                                      on-confirm="executeConfirm()"
                                      on-cancel="closeModal()"
                                      :confirm-variant="$confirmVariant"
                                      :confirm-color="$confirmColor"
                                      :cancel-variant="$cancelVariant"
                                      :cancel-color="$cancelColor" />
        @else
            {{ $slot }}
        @endif

        {{-- Footer - Legacy/Slot Support --}}
        @if (isset($actions))
            <x-slot:footer-actions>
                {{ $actions }}
            </x-slot:footer-actions>
        @endif
    </x-ui.base-modal>
</div>
@assets
    <script @cspNonce>
        (function() {
            const register = function() {
                Alpine.data('confirmModalData', function(config) {
                    const parsedConfig = JSON.parse(config);
                    return {
                        isOpen: false,
                        modalId: parsedConfig.modalId,
                        title: parsedConfig.title,
                        message: parsedConfig.message,
                        confirmLabel: parsedConfig.confirmLabel,
                        cancelLabel: parsedConfig.cancelLabel,
                        callback: null,
                        payload: null,

                        handleConfirmModal(event) {
                            const data = event.detail;
                            if (data.modalId && data.modalId !== this.modalId) return;

                            this.title = data.title || parsedConfig.title;
                            this.message = data.message || parsedConfig.message;
                            this.confirmLabel = data.confirmLabel || parsedConfig.confirmLabel;
                            this.cancelLabel = data.cancelLabel || parsedConfig.cancelLabel;
                            this.callback = data.callback;
                            this.payload = data.payload || null;
                            this.isOpen = true;
                        },

                        executeConfirm() {
                            if (this.callback) {
                                if (typeof this.callback === 'string') {
                                    window.dispatchEvent(new CustomEvent(this.callback, {
                                        detail: this.payload
                                    }));
                                } else if (typeof this.callback === 'function') {
                                    this.callback(this.payload);
                                }
                            }
                            this.closeModal();
                        },

                        closeModal() {
                            this.isOpen = false;
                        }
                    };
                });
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
