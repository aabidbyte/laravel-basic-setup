@props([
    'actions' => [],
    'itemUuid' => '',
])

<div class="flex items-center gap-2" wire:click.stop>
    @foreach ($actions as $action)
        @if (($action['key'] ?? null) === 'delete')
            <x-ui.button
                style="ghost"
                color="error"
                size="sm"
                @click="$dispatch('confirm-modal', {
                    title: '{{ addslashes($action['label'] ?? __('ui.actions.delete')) }}',
                    message: '{{ addslashes(__('ui.modals.confirm.message')) }}',
                    confirmAction: () => $wire.handleRowAction('{{ $action['key'] }}', '{{ $itemUuid }}')
                })"
            >
                @if (isset($action['icon']))
                    <x-ui.icon name="{{ $action['icon'] }}" class="h-4 w-4" />
                @endif
            </x-ui.button>
        @else
            <x-ui.button
                style="{{ $action['variant'] ?? 'ghost' }}"
                color="{{ $action['color'] ?? null }}"
                size="sm"
                wire:click.stop="handleRowAction('{{ $action['key'] }}', '{{ $itemUuid }}')"
            >
                @if (isset($action['icon']))
                    <x-ui.icon name="{{ $action['icon'] }}" class="h-4 w-4" />
                @endif
            </x-ui.button>
        @endif
    @endforeach
</div>

