<thead>
    <tr>
        {{-- Select All Checkbox - only render if bulk actions are defined --}}
        @if ($datatable->hasBulkActions())
            <th class="w-12">
                <x-ui.checkbox wire:click="toggleSelectAll()"
                               :checked="$datatable->isAllSelected"
                               wire:key="select-all-checkbox-{{ $datatable->isAllSelected ? '1' : '0' }}"
                               size="sm" />
            </th>
        @endif

        {{-- Column Headers --}}
        @foreach ($datatable->getColumns() as $column)
            @php
                $columnStyles = $column['width'] ? "width: {$column['width']}; max-width: {$column['width']};" : '';
                $columnClasses = [
                    'whitespace-nowrap' => $column['nowrap'],
                    'truncate' => (bool) $column['width'],
                ];
            @endphp

            @if ($column['sortable'])
                <th wire:click="sort('{{ $column['field'] }}')"
                    style="{{ $columnStyles }}"
                    @class([
                        'cursor-pointer select-none hover:bg-base-200',
                        ...$columnClasses,
                    ])>
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate">{{ $column['label'] }}</span>
                        @if ($datatable->sortBy === $column['field'])
                            <x-ui.icon :name="$datatable->sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'"
                                       size="xs"></x-ui.icon>
                        @endif
                    </div>
                </th>
            @else
                <th style="{{ $columnStyles }}"
                    @class($columnClasses)>
                    <div class="flex items-center gap-2">
                        <span class="truncate">{{ $column['label'] }}</span>
                    </div>
                </th>
            @endif
        @endforeach

        {{-- Actions Column - only render if row actions are defined --}}
        @if ($datatable->hasRowActions())
            <th class="bg-base-100 sticky right-0 z-20 text-end">{{ __('table.actions') }}</th>
        @endif
    </tr>
</thead>
