@props([
    'columns' => [],
    'sortBy' => null,
    'sortDirection' => 'asc',
    'showBulk' => false,
    'selectPage' => false,
    'selectAll' => false,
    'showActions' => false,
])

@php
    // Process header columns
    $processHeaderColumn = function (array $column): array {
        $hidden = $column['hidden'] ?? false;
        $responsive = $column['responsive'] ?? null;
        $thClass = $responsive ? 'hidden '.$responsive.':table-cell' : '';
        $columnKey = $column['key'] ?? null;
        $sortable = ($column['sortable'] ?? false) && $columnKey;

        return [
            'hidden' => $hidden,
            'responsive' => $responsive,
            'thClass' => $thClass,
            'columnKey' => $columnKey,
            'sortable' => $sortable,
        ];
    };
@endphp

<thead>
    <tr>
        @if ($showBulk)
            <th>
                <input type="checkbox" wire:model="selectPage" wire:click="toggleSelectPage" class="checkbox checkbox-sm" />
            </th>
        @endif

        @foreach ($columns as $column)
            @php
                $columnData = $processHeaderColumn($column);
            @endphp
            @if (!$columnData['hidden'])
                <th @class([$columnData['thClass'] => $columnData['responsive']])>
                    @if ($columnData['sortable'] && $columnData['columnKey'])
                        <button wire:click="sortBy('{{ $columnData['columnKey'] }}')"
                            class="flex items-center gap-2 hover:text-primary">
                            <span>{{ $column['label'] ?? '' }}</span>
                            @if ($sortBy === $columnData['columnKey'])
                                <x-ui.icon
                                    name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                    class="h-4 w-4"></x-ui.icon>
                            @else
                                <x-ui.icon name="arrows-up-down" class="h-4 w-4 opacity-30"></x-ui.icon>
                            @endif
                        </button>
                    @else
                        {{ $column['label'] ?? '' }}
                    @endif
                </th>
            @endif
        @endforeach

        @if ($showActions)
            <th></th>
        @endif
    </tr>
</thead>
