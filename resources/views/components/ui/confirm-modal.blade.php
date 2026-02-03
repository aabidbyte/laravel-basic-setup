@assets
    <script>
        (() => {
            const init = () => {
                window.Alpine.data('confirmModalData', (defaultConfig = {}) => ({
                    isOpen: false,
                    title: defaultConfig.title || 'Confirm Action',
                    message: defaultConfig.message || 'Are you sure?',
                    confirmLabel: defaultConfig.confirmLabel || 'Confirm',
                    cancelLabel: defaultConfig.cancelLabel || 'Cancel',
                    confirmEvent: null,
                    confirmData: null,
                    confirmAction: null,

                    handleConfirmModal(event) {
                        if (!event.detail) return;
                        const cfg = event.detail;
                        if (cfg.title) this.title = cfg.title;
                        if (cfg.message || cfg.content) this.message = cfg.message || cfg.content;
                        if (cfg.confirmLabel || cfg.confirmText) this.confirmLabel = cfg.confirmLabel ||
                            cfg.confirmText;
                        if (cfg.cancelLabel || cfg.cancelText) this.cancelLabel = cfg.cancelLabel || cfg
                            .cancelText;
                        this.confirmEvent = cfg.confirmEvent || null;
                        this.confirmData = cfg.confirmData || null;
                        this.confirmAction = cfg.confirmAction || null;
                        if (this.confirmAction) window._confirmModalAction = this.confirmAction;
                        this.isOpen = true;
                    },

                    executeConfirm() {
                        if (this.confirmEvent) {
                            window.dispatchEvent(new CustomEvent(this.confirmEvent, {
                                detail: this.confirmData,
                                bubbles: true
                            }));
                        }
                        let action = this.confirmAction || window._confirmModalAction;
                        if (action && typeof action === 'function') action();
                        this.closeModal();
                    },

                    closeModal() {
                        this.isOpen = false;
                        this.confirmAction = null;
                        this.confirmEvent = null;
                        window._confirmModalAction = null;
                    }
                }));
            };
            if (window.Alpine) {
                init();
            } else {
                document.addEventListener('alpine:init', init);
            }
        })();
    </script>
@endassets

{{-- Confirm Modal Component --}}
@php
    $modalStateId = $openState ?? 'confirmModalIsOpen_' . str_replace('-', '_', $id);
@endphp

<div x-show="isOpen" x-data="confirmModalData({
    modalId: '{{ $id }}',
    title: @js(__('modals.confirm.title')),
    message: @js(__('modals.confirm.message')),
    confirmLabel: @js(__('actions.confirm')),
    cancelLabel: @js(__('actions.cancel'))
})"
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
