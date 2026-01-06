{{-- Confirm Modal Component --}}
@php
    $modalStateId = $openState ?? 'confirmModalIsOpen_' . str_replace('-', '_', $id);
@endphp

<div
    x-data="confirmModal({
        modalId: '{{ $id }}',
        title: @js(__('ui.modals.confirm.title')),
        message: @js(__('ui.modals.confirm.message')),
        confirmLabel: @js(__('ui.actions.confirm')),
        cancelLabel: @js(__('ui.actions.cancel'))
    })"
    @confirm-modal.window="handleConfirmModal($event)"
    @confirm-modal-execute.window="executeConfirm()"
    @confirm-modal-cancel.window="closeModal()"
>

    <x-ui.base-modal
        :id="$id"
        open-state="isOpen"
        :use-parent-state="true"
        max-width="{{ $maxWidth }}"
        :placement="$placement"
        :show-close-button="false"
        :show-footer="true"
        :close-on-outside-click="$closeOnOutsideClick"
        :close-on-escape="$closeOnEscape"
        :backdrop-transition="$backdropTransition"
    >

        {{-- Body --}}
        @if ($slot->isEmpty())
            <div class="flex items-start gap-4">
                @if ($showIcon)
                    <div class="flex-shrink-0">
                        <x-ui.icon
                            name="exclamation-triangle"
                            class="h-8 w-8 text-error"
                        ></x-ui.icon>
                    </div>
                @endif
                <div class="flex-1">
                    <h3
                        class="text-lg font-bold"
                        x-text="title"
                    ></h3>
                    <p
                        class="py-4 text-base-content/70"
                        x-text="message"
                    ></p>
                </div>
            </div>
        @else
            {{ $slot }}
        @endif

        {{-- Footer --}}
        <x-slot:footer-actions>
            @if (!isset($actions))
                <x-ui.button
                    type="button"
                    :variant="$cancelVariant"
                    @click="closeModal()"
                >
                    <span x-text="cancelLabel"></span>
                </x-ui.button>
                <x-ui.button
                    type="button"
                    :variant="$confirmVariant"
                    @click="executeConfirm()"
                >
                    <span x-text="confirmLabel"></span>
                </x-ui.button>
            @else
                {{ $actions }}
            @endif
        </x-slot:footer-actions>
    </x-ui.base-modal>
</div>
