<thead>
    <tr>
        {{-- Select All Checkbox --}}
        <th class="w-12">
            <x-ui.checkbox
                wire:click="toggleSelectAll()"
                :checked="$this->isAllSelected"
                wire:key="select-all-checkbox-{{ $this->isAllSelected ? '1' : '0' }}"
                size="sm"
            />
        </th>

        {{-- Column Headers --}}
        @foreach ($this->getColumns() as $column)
            @php
                $columnStyles = $column['width'] ? "width: {$column['width']}; max-width: {$column['width']};" : '';
                $columnClasses = [
                    'whitespace-nowrap' => $column['nowrap'],
                    'truncate' => (bool) $column['width'],
                ];
            @endphp

            @if ($column['sortable'])
                <th
                    wire:click="sort('{{ $column['field'] }}')"
                    style="{{ $columnStyles }}"
                    @class([
                        'cursor-pointer select-none hover:bg-base-200',
                        ...$columnClasses,
                    ])
                >
                    <div class="flex items-center gap-2 justify-between">
                        <span class="truncate">{{ $column['label'] }}</span>
                        @if ($this->sortBy === $column['field'])
                            <x-ui.icon
                                :name="$this->sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'"
                                size="xs"
                            ></x-ui.icon>
                        @endif
                    </div>
                </th>
            @else
                <th
                    style="{{ $columnStyles }}"
                    @class($columnClasses)
                >
                    <div class="flex items-center gap-2">
                        <span class="truncate">{{ $column['label'] }}</span>
                    </div>
                </th>
            @endif
        @endforeach

        {{-- Actions Column --}}
        <th></th>
    </tr>
</thead>
