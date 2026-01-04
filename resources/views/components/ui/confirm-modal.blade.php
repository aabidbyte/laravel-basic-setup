{{-- Confirm Modal Component --}}
@php
    $modalStateId = $openState ?? 'confirmModalIsOpen_' . str_replace('-', '_', $id);
@endphp

<div
    x-data="{
        modalId: '{{ $id }}',
        @if (!$useExternalState) {{ $modalStateId }}: false, @endif
        title: @js(__('ui.modals.confirm.title')),
        message: @js(__('ui.modals.confirm.message')),
        confirmLabel: @js(__('ui.actions.confirm')),
        cancelLabel: @js(__('ui.actions.cancel')),
        confirmEvent: null,
        confirmData: null,
        confirmAction: null,
    
        isOpen() {
            @if ($useExternalState) let parentData = $el.parentElement?.closest('[x-data]')?.__x;
            return parentData?.$data?.['{{ $openState }}'] ?? false;
        @else
            return this.{{ $modalStateId }}; @endif
        },
    
        closeModal() {
            @if ($useExternalState) let parentData = $el.parentElement?.closest('[x-data]')?.__x;
            if (parentData && parentData.$data && parentData.$data['{{ $openState }}'] !== undefined) {
                parentData.$data['{{ $openState }}'] = false;
            }
        @else
            this.{{ $modalStateId }} = false; @endif
            this.confirmAction = null;
            this.confirmEvent = null;
            window._confirmModalAction = null;
        },
    
        executeConfirm() {
            // 1. Dispatch back-event if provided
            if (this.confirmEvent) {
                window.dispatchEvent(new CustomEvent(this.confirmEvent, {
                    detail: this.confirmData,
                    bubbles: true
                }));
            }
    
            // 2. Execute closure if provided (fallback)
            let action = this.confirmAction || window._confirmModalAction;
            if (action && typeof action === 'function') {
                action();
            }
    
            this.closeModal();
        },
    
        handleConfirmModal(event) {
            if (event.detail) {
                let config = event.detail;
                if (config.title) this.title = config.title;
                if (config.message || config.content) this.message = config.message || config.content;
                if (config.confirmLabel || config.confirmText) this.confirmLabel = config.confirmLabel || config.confirmText;
                if (config.cancelLabel || config.cancelText) this.cancelLabel = config.cancelLabel || config.cancelText;
    
                this.confirmEvent = config.confirmEvent || null;
                this.confirmData = config.confirmData || null;
                this.confirmAction = config.confirmAction || null;
                if (this.confirmAction) window._confirmModalAction = this.confirmAction;
    
                @if ($useExternalState) let parentData = $el.parentElement?.closest('[x-data]')?.__x;
                if (parentData && parentData.$data && parentData.$data['{{ $openState }}'] !== undefined) {
                    parentData.$data['{{ $openState }}'] = true;
                }
            @else
                this.{{ $modalStateId }} = true; @endif
            }
        }
    }"
    @confirm-modal.window="handleConfirmModal($event)"
    @confirm-modal-execute.window="executeConfirm()"
    @confirm-modal-cancel.window="closeModal()"
>

    <x-ui.base-modal
        :id="$id"
        :open-state="$useExternalState ? $openState : $modalStateId"
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
