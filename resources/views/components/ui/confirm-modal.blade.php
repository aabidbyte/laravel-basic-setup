{{-- Confirm Modal Component --}}
@php
    $modalStateId = $openState ?? 'confirmModalIsOpen_' . str_replace('-', '_', $id);
@endphp

<div x-data="confirmModal({
    modalId: '{{ $id }}',
    title: @js(__('modals.confirm.title')),
    message: @js(__('modals.confirm.message')),
    confirmLabel: @js(__('actions.confirm')),
    cancelLabel: @js(__('actions.cancel'))
})"
     @confirm-modal.window="handleConfirmModal($event)"
     @confirm-modal-execute.window="executeConfirm()"
     @confirm-modal-cancel.window="closeModal()">

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
