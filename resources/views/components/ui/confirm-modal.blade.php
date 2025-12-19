@props([
    'id' => 'confirm-modal',
    'confirmVariant' => 'error',
    'cancelVariant' => 'ghost',
    'maxWidth' => 'md',
    'placement' => 'middle',
    'showIcon' => true,
])

<div x-data
    @confirm-modal.window="
    const modal = document.getElementById('{{ $id }}');
    if (modal && $event.detail) {
        const config = $event.detail;
        if (config.confirmAction) window._confirmModalAction = config.confirmAction;
        $nextTick(() => modal.showModal());
    }
">
    <x-ui.modal :id="$id" max-width="{{ $maxWidth }}" placement="{{ $placement }}" :show-close-button="false"
        :close-btn="false">
        @if ($slot->isEmpty())
            <div x-data="{
                title: '{{ __('ui.modals.confirm.title') }}',
                message: '{{ __('ui.modals.confirm.message') }}',
                confirmLabel: '{{ __('ui.actions.confirm') }}',
                cancelLabel: '{{ __('ui.actions.cancel') }}'
            }"
                @confirm-modal.window="
                if ($event.detail) {
                    const config = $event.detail;
                    if (config.title) title = config.title;
                    if (config.message) message = config.message;
                    if (config.confirmLabel) confirmLabel = config.confirmLabel;
                    if (config.cancelLabel) cancelLabel = config.cancelLabel;
                }
            ">
                <div class="flex items-start gap-4">
                    @if ($showIcon)
                        <div class="flex-shrink-0">
                            <x-ui.icon name="exclamation-triangle" class="h-8 w-8 text-error" />
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

        <x-slot:actions>
            @if (!isset($actions))
                <form method="dialog">
                    <x-ui.button type="button" :variant="$cancelVariant"
                        onclick="document.getElementById('{{ $id }}').close(); window._confirmModalAction = null;">
                        <span x-data="{
                            cancelLabel: '{{ __('ui.actions.cancel') }}'
                        }"
                            @confirm-modal.window="
                            if ($event.detail?.cancelLabel) cancelLabel = $event.detail.cancelLabel;
                        "
                            x-text="cancelLabel"></span>
                    </x-ui.button>
                </form>
                <x-ui.button type="button" :variant="$confirmVariant"
                    onclick="if (window._confirmModalAction && typeof window._confirmModalAction === 'function') window._confirmModalAction(); document.getElementById('{{ $id }}').close(); window._confirmModalAction = null;">
                    <span x-data="{
                        confirmLabel: '{{ __('ui.actions.confirm') }}'
                    }"
                        @confirm-modal.window="
                        if ($event.detail?.confirmLabel) confirmLabel = $event.detail.confirmLabel;
                    "
                        x-text="confirmLabel"></span>
                </x-ui.button>
            @else
                {{ $actions }}
            @endif
        </x-slot:actions>
    </x-ui.modal>
</div>
