@props([
    'viewData' => null,
    'columns' => [],
    'sortBy' => null,
    'sortDirection' => 'asc',
    'showBulk' => false,
    'selectPage' => false,
    'selectAll' => false,
    'showActions' => false,
])

@php
    use App\Services\DataTable\View\DataTableViewData;

    // If viewData is provided, use it; otherwise create from props (backward compatibility)
    if (!$viewData) {
        $viewData = new DataTableViewData(
            rows: [],
            headers: $columns,
            columns: [],
            actionsPerRow: [],
            bulkActions: [],
            filters: [],
            selected: [],
            rowClick: null,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
            showBulk: $showBulk,
            selectPage: $selectPage,
            selectAll: $selectAll,
            showSearch: false,
            searchPlaceholder: null,
            paginator: null,
            emptyMessage: null,
            emptyIcon: 'user-group',
            class: '',
            openRowActionModal: null,
            openRowActionUuid: null,
            openBulkActionModal: null,
        );
    }
@endphp

<thead>
    <tr>
        @if ($viewData->isShowBulk())
            <th>
                <input type="checkbox" wire:model="selectPage" wire:click="toggleSelectPage" class="checkbox checkbox-sm" />
            </th>
        @endif

        @foreach ($viewData->getHeaders() as $column)
            @php
                $columnData = $viewData->processHeaderColumn($column);
            @endphp
            @if (!$columnData['hidden'])
                <th @class([$columnData['thClass'] => $columnData['responsive']])>
                    @if ($columnData['sortable'] && $columnData['columnKey'])
                        <button wire:click="sortBy('{{ $columnData['columnKey'] }}')"
                            class="flex items-center gap-2 hover:text-primary">
                            <span>{{ $column['label'] ?? '' }}</span>
                            @if ($viewData->getSortBy() === $columnData['columnKey'])
                                <x-ui.icon
                                    name="{{ $viewData->getSortDirection() === 'asc' ? 'chevron-up' : 'chevron-down' }}"
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

        @if ($viewData->hasActionsPerRow())
            <th></th>
        @endif
    </tr>
</thead>
