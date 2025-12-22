@props([
    'selectedCount' => 0,
    'bulkActions' => [],
])

<div class="mb-4 flex items-center justify-between rounded-lg bg-base-200 p-4">
    <div class="text-sm font-medium">
        {{ __('ui.table.selected_count', ['count' => $selectedCount]) }}
    </div>

    <div class="flex items-center gap-2">
        @foreach ($bulkActions as $action)
            @if (($action['key'] ?? null) === 'delete')
                <x-ui.button
                    style="{{ $action['variant'] ?? 'ghost' }}"
                    color="{{ $action['color'] ?? 'error' }}"
                    size="sm"
                    @click="$dispatch('confirm-modal', {
                        title: '{{ addslashes($action['label'] ?? __('ui.actions.delete_selected')) }}',
                        message: '{{ addslashes(__('ui.modals.confirm.message')) }}',
                        confirmAction: () => $wire.handleBulkAction('{{ $action['key'] }}')
                    })"
                >
                    {{ $action['label'] ?? __('ui.actions.delete_selected') }}
                </x-ui.button>
            @else
                <x-ui.button
                    style="{{ $action['variant'] ?? 'ghost' }}"
                    color="{{ $action['color'] ?? null }}"
                    size="sm"
                    wire:click="handleBulkAction('{{ $action['key'] }}')"
                >
                    {{ $action['label'] ?? '' }}
                </x-ui.button>
            @endif
        @endforeach

        <x-ui.button
            style="ghost"
            size="sm"
            wire:click="clearSelection"
        >
            {{ __('ui.actions.clear_selection') }}
        </x-ui.button>
    </div>
</div>

