<x-ui.dropdown placement="end" menu menuSize="sm" teleport>
    <x-slot:trigger>
        <x-ui.button type="button" size="sm" class="btn-square">
            <x-ui.icon name="ellipsis-vertical" size="sm"></x-ui.icon>
        </x-ui.button>
    </x-slot:trigger>

    @foreach ($this->getRowActionsForRow($row) as $action)
        @php
            $colorClass = match($action['color'] ?? null) {
                'error' => 'text-error hover:bg-error/10',
                'warning' => 'text-warning hover:bg-warning/10',
                'success' => 'text-success hover:bg-success/10',
                'info' => 'text-info hover:bg-info/10',
                'primary' => 'text-primary hover:bg-primary/10',
                'secondary' => 'text-secondary hover:bg-secondary/10',
                default => 'text-base hover:bg-base-300'
            };

            $baseClasses = "flex items-center gap-2 w-full cursor-pointer px-2 py-1 rounded $colorClass";
        @endphp

        @if ($action['hasRoute'])
            <a href="{{ $action['route'] }}" wire:navigate class="{{ $baseClasses }}">
                @if ($action['icon'])
                    <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                @endif
                {{ $action['label'] }}
            </a>
        @elseif ($action['hasModal'])
            {{-- Dispatch loading event immediately, then make Livewire request --}}
            <button type="button" 
                @click="window.dispatchEvent(new CustomEvent('datatable-modal-loading')); $wire.openActionModal('{{ $action['key'] }}', '{{ $row->uuid }}')"
                class="{{ $baseClasses }}">
                @if ($action['icon'])
                    <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                @endif
                {{ $action['label'] }}
            </button>
        @elseif ($action['confirm'])
            <button type="button" @click="executeActionWithConfirmation('{{ $action['key'] }}', '{{ $row->uuid }}', false)"
                class="{{ $baseClasses }}">
                @if ($action['icon'])
                    <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                @endif
                {{ $action['label'] }}
            </button>
        @else
            <button type="button" wire:click="executeAction('{{ $action['key'] }}', '{{ $row->uuid }}')"
                class="{{ $baseClasses }}">
                @if ($action['icon'])
                    <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                @endif
                {{ $action['label'] }}
            </button>
        @endif
    @endforeach
</x-ui.dropdown>
