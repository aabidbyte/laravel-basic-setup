<x-ui.dropdown placement="end" menu menuSize="sm" teleport>
    <x-slot:trigger>
        <x-ui.button type="button" size="sm" class="btn-square">
            <x-ui.icon name="ellipsis-vertical" size="sm"></x-ui.icon>
        </x-ui.button>
    </x-slot:trigger>

    @foreach ($this->getRowActionsForRow($row) as $action)
        @if ($action['hasRoute'])
                <a href="{{ $action['route'] }}" wire:navigate
                    class="flex items-center gap-2">
                    @if ($action['icon'])
                        <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                    @endif
                    {{ $action['label'] }}
                </a>
        @else
            @if ($action['hasModal'])
                    <x-ui.button
                        wire:click="openActionModal('{{ $action['key'] }}', '{{ $row->uuid }}')"
                        type="button" class="flex items-center gap-2 w-full">
                        @if ($action['icon'])
                            <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                        @endif
                        {{ $action['label'] }}
                    </x-ui.button>
            @elseif ($action['confirm'])
                    <x-ui.button
                        @click="executeActionWithConfirmation('{{ $action['key'] }}', '{{ $row->uuid }}', false)"
                        type="button" class="flex items-center gap-2 w-full" variant="link">
                        @if ($action['icon'])
                            <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                        @endif
                        {{ $action['label'] }}
                    </x-ui.button>
            @else
                    <x-ui.button
                        wire:click="executeAction('{{ $action['key'] }}', '{{ $row->uuid }}')"
                        type="button" class="flex items-center gap-2 w-full">
                        @if ($action['icon'])
                            <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                        @endif
                        {{ $action['label'] }}
                    </x-ui.button>
            @endif
        @endif
    @endforeach
</x-ui.dropdown>
