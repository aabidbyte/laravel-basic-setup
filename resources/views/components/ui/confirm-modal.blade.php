{{-- Confirm Modal: Uses external state when openState prop is provided, otherwise creates internal state --}}
@if ($useExternalState)
    <div x-data="{
        modalId: '{{ $id }}',
        confirmAction: null,
        closeModal() {
            let parentData = $el.parentElement?.closest('[x-data]')?.__x;
            if (parentData && parentData.$data && parentData.$data['{{ $openState }}'] !== undefined) {
                parentData.$data['{{ $openState }}'] = false;
            }
            if (this.confirmAction) this.confirmAction = null;
            window._confirmModalAction = null;
        },
        executeConfirm() {
            let action = this.confirmAction || window._confirmModalAction;
            if (action && typeof action === 'function') {
                action();
            }
            let parentData = $el.parentElement?.closest('[x-data]')?.__x;
            if (parentData && parentData.$data && parentData.$data['{{ $openState }}'] !== undefined) {
                parentData.$data['{{ $openState }}'] = false;
            }
            if (this.confirmAction) this.confirmAction = null;
            window._confirmModalAction = null;
        },
        handleConfirmModal(event) {
            if (event.detail) {
                let config = event.detail;
                if (config.confirmAction) {
                    this.confirmAction = config.confirmAction;
                    window._confirmModalAction = config.confirmAction;
                }
                let parentData = $el.parentElement?.closest('[x-data]')?.__x;
                if (parentData && parentData.$data && parentData.$data['{{ $openState }}'] !== undefined) {
                    parentData.$data['{{ $openState }}'] = true;
                }
            }
        }
    }" @confirm-modal.window="handleConfirmModal($event)">
        <x-ui.base-modal :id="$id" :open-state="$openState" :use-parent-state="true" max-width="{{ $maxWidth }}"
            :placement="$placement" :show-close-button="false" :show-footer="true"
            :close-on-outside-click="$closeOnOutsideClick" :close-on-escape="$closeOnEscape"
            :backdrop-transition="$backdropTransition">
            @if ($slot->isEmpty())
                <div x-data="{
                    title: @js(__('ui.modals.confirm.title')),
                    message: @js(__('ui.modals.confirm.message')),
                    confirmLabel: @js(__('ui.actions.confirm')),
                    cancelLabel: @js(__('ui.actions.cancel')),
                    handleConfirmModal(event) {
                        if (event.detail) {
                            let config = event.detail;
                            if (config.title) this.title = config.title;
                            if (config.message) this.message = config.message;
                            if (config.confirmLabel) this.confirmLabel = config.confirmLabel;
                            if (config.cancelLabel) this.cancelLabel = config.cancelLabel;
                        }
                    }
                }" @confirm-modal.window="handleConfirmModal($event)">
                    <div class="flex items-start gap-4">
                        @if ($showIcon)
                            <div class="flex-shrink-0">
                                <x-ui.icon name="exclamation-triangle" class="h-8 w-8 text-error"></x-ui.icon>
                            </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="text-lg font-bold" x-text="title"></h3>
                            <p class="py-4 text-base-content/70" x-text="message"></p>
                        </div>
                    </div>
                </div>
            @else
                {{ $slot }}
            @endif

            <x-slot:footer-actions>
                @if (!isset($actions))
                    <x-ui.button type="button" :variant="$cancelVariant" @click="closeModal()">
                        <span x-data="{
                            cancelLabel: @js(__('ui.actions.cancel'))
                        }"
                            @confirm-modal.window="
                            if ($event.detail?.cancelLabel) cancelLabel = $event.detail.cancelLabel;
                        "
                            x-text="cancelLabel"></span>
                    </x-ui.button>
                    <x-ui.button type="button" :variant="$confirmVariant" @click="executeConfirm()">
                        <span x-data="{
                            confirmLabel: @js(__('ui.actions.confirm'))
                        }"
                            @confirm-modal.window="
                            if ($event.detail?.confirmLabel) confirmLabel = $event.detail.confirmLabel;
                        "
                            x-text="confirmLabel"></span>
                    </x-ui.button>
                @else
                    {{ $actions }}
                @endif
            </x-slot:footer-actions>
        </x-ui.base-modal>
    </div>
@else
    <div x-data="{
        modalId: '{{ $id }}',
        {{ $modalStateId }}: false,
        confirmAction: null,
        closeModal() {
            this.{{ $modalStateId }} = false;
            if (this.confirmAction) this.confirmAction = null;
            window._confirmModalAction = null;
        },
        executeConfirm() {
            let action = this.confirmAction || window._confirmModalAction;
            if (action && typeof action === 'function') {
                action();
            }
            this.{{ $modalStateId }} = false;
            if (this.confirmAction) this.confirmAction = null;
            window._confirmModalAction = null;
        },
        handleConfirmModal(event) {
            if (event.detail) {
                let config = event.detail;
                if (config.confirmAction) {
                    this.confirmAction = config.confirmAction;
                    window._confirmModalAction = config.confirmAction;
                }
                this.{{ $modalStateId }} = true;
            }
        }
    }" @confirm-modal.window="handleConfirmModal($event)">
        <x-ui.base-modal :id="$id" :open-state="$modalStateId" :use-parent-state="true" max-width="{{ $maxWidth }}"
            :placement="$placement" :show-close-button="false" :show-footer="true"
            :close-on-outside-click="$closeOnOutsideClick" :close-on-escape="$closeOnEscape"
            :backdrop-transition="$backdropTransition">
            @if ($slot->isEmpty())
                <div x-data="{
                    title: @js(__('ui.modals.confirm.title')),
                    message: @js(__('ui.modals.confirm.message')),
                    confirmLabel: @js(__('ui.actions.confirm')),
                    cancelLabel: @js(__('ui.actions.cancel')),
                    handleConfirmModal(event) {
                        if (event.detail) {
                            let config = event.detail;
                            if (config.title) this.title = config.title;
                            if (config.message) this.message = config.message;
                            if (config.confirmLabel) this.confirmLabel = config.confirmLabel;
                            if (config.cancelLabel) this.cancelLabel = config.cancelLabel;
                        }
                    }
                }" @confirm-modal.window="handleConfirmModal($event)">
                    <div class="flex items-start gap-4">
                        @if ($showIcon)
                            <div class="flex-shrink-0">
                                <x-ui.icon name="exclamation-triangle" class="h-8 w-8 text-error"></x-ui.icon>
                            </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="text-lg font-bold" x-text="title"></h3>
                            <p class="py-4 text-base-content/70" x-text="message"></p>
                        </div>
                    </div>
                </div>
            @else
                {{ $slot }}
            @endif

            <x-slot:footer-actions>
                @if (!isset($actions))
                    <x-ui.button type="button" :variant="$cancelVariant" @click="closeModal()">
                        <span x-data="{
                            cancelLabel: @js(__('ui.actions.cancel'))
                        }"
                            @confirm-modal.window="
                            if ($event.detail?.cancelLabel) cancelLabel = $event.detail.cancelLabel;
                        "
                            x-text="cancelLabel"></span>
                    </x-ui.button>
                    <x-ui.button type="button" :variant="$confirmVariant" @click="executeConfirm()">
                        <span x-data="{
                            confirmLabel: @js(__('ui.actions.confirm'))
                        }"
                            @confirm-modal.window="
                            if ($event.detail?.confirmLabel) confirmLabel = $event.detail.confirmLabel;
                        "
                            x-text="confirmLabel"></span>
                    </x-ui.button>
                @else
                    {{ $actions }}
                @endif
            </x-slot:footer-actions>
        </x-ui.base-modal>
    </div>
@endif
