@props([
    'actions' => [],
    'itemUuid' => '',
])

@php
    use App\Constants\DataTable\DataTableUi;
@endphp

@if (count($actions) > 0)
    <x-ui.dropdown placement="end" menu>
        <x-slot:trigger>
            <x-ui.button variant="ghost" size="sm" wire:click.stop>
                <x-ui.icon name="{{ DataTableUi::ICON_THREE_DOTS }}" class="h-4 w-4"></x-ui.icon>
            </x-ui.button>
        </x-slot:trigger>

        @foreach ($actions as $action)
            <li>
                @if (($action['key'] ?? null) === 'delete')
                    <button
                        type="button"
                        class="w-full text-left text-error"
                        @click="$dispatch('confirm-modal', {
                            title: '{{ addslashes($action['label'] ?? __('ui.actions.delete')) }}',
                            message: '{{ addslashes(__('ui.modals.confirm.message')) }}',
                            confirmAction: () => $wire.handleRowAction('{{ $action['key'] }}', '{{ $itemUuid }}')
                        })"
                    >
                        @if (isset($action['icon']))
                            <x-ui.icon name="{{ $action['icon'] }}" class="h-4 w-4 mr-2"></x-ui.icon>
                        @endif
                        {{ $action['label'] ?? __('ui.actions.delete') }}
                    </button>
                @else
                    <button
                        type="button"
                        class="w-full text-left"
                        wire:click.stop="handleRowAction('{{ $action['key'] }}', '{{ $itemUuid }}')"
                    >
                        @if (isset($action['icon']))
                            <x-ui.icon name="{{ $action['icon'] }}" class="h-4 w-4 mr-2"></x-ui.icon>
                        @endif
                        {{ $action['label'] ?? '' }}
                    </button>
                @endif
            </li>
        @endforeach
    </x-ui.dropdown>
@endif

