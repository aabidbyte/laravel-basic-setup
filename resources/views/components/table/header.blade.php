@props([
    'columns' => [],
    'sortBy' => null,
    'sortDirection' => 'asc',
    'showBulk' => false,
    'selectPage' => false,
    'selectAll' => false,
])

<thead>
    <tr>
        @if ($showBulk)
            <th>
                <input
                    type="checkbox"
                    wire:model="selectPage"
                    wire:click="toggleSelectPage"
                    class="checkbox checkbox-sm"
                />
            </th>
        @endif

        @foreach ($columns as $column)
            <th>
                @if ($column['sortable'] ?? false)
                    <button
                        wire:click="sortBy('{{ $column['key'] }}')"
                        class="flex items-center gap-2 hover:text-primary"
                    >
                        <span>{{ $column['label'] }}</span>
                        @if ($sortBy === $column['key'])
                            <x-ui.icon
                                name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                class="h-4 w-4"
                            />
                        @else
                            <x-ui.icon name="arrows-up-down" class="h-4 w-4 opacity-30" />
                        @endif
                    </button>
                @else
                    {{ $column['label'] }}
                @endif
            </th>
        @endforeach

        <th>{{ __('ui.table.actions') }}</th>
    </tr>
</thead>

