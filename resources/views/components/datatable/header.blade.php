<thead wire:key="header-{{ $this->datatableId }}">
    <tr wire:key="header-{{ $this->datatableId }}-row">
        {{-- Select All Checkbox - only render if bulk actions are defined --}}
        @if ($this->hasBulkActions())
            <th wire:key="header-{{ $this->datatableId }}-col-checkbox"
                class="bg-base-100 sticky left-0 z-20 w-4 p-1">
                <x-ui.checkbox @click="toggleAll()"
                               wire:bind:checked="currentPageUuids.length > 0 && selected.length === currentPageUuids.length"
                               wire:key="select-all-checkbox"
                               size="xs" />
            </th>
        @endif

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
                <th wire:key="header-{{ $this->datatableId }}-col-{{ $column['field'] }}"
                    wire:click="sort('{{ $column['field'] }}')"
                    style="{{ $columnStyles }}"
                    @class([
                        'cursor-pointer select-none hover:bg-base-200',
                        ...$columnClasses,
                    ])>
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate">{{ $column['label'] }}</span>
                        @if ($this->sortBy === $column['field'])
                            <x-ui.icon :name="$this->sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'"
                                       size="xs"></x-ui.icon>
                        @endif
                    </div>
                </th>
            @else
                <th wire:key="header-{{ $this->datatableId }}-col-{{ $column['field'] ?? $loop->index }}"
                    style="{{ $columnStyles }}"
                    @class($columnClasses)>
                    <div class="flex items-center gap-2">
                        <span class="truncate">{{ $column['label'] }}</span>
                    </div>
                </th>
            @endif
        @endforeach

        {{-- Actions Column - only render if row actions are defined --}}
        @if ($this->hasRowActions())
            <th wire:key="header-{{ $this->datatableId }}-col-actions"
                class="bg-base-100 sticky right-0 z-20 text-end">{{ __('table.actions') }}</th>
        @endif
    </tr>
</thead>
