@props([
    'viewData' => null,
    'class' => '',
    'rows' => [],
    'headers' => [],
    'columns' => [],
    'actionsPerRow' => [],
    'rowClick' => null,
    'sortBy' => null,
    'sortDirection' => 'asc',
    'showBulk' => false,
    'selectPage' => false,
    'selectAll' => false,
    'selected' => [],
    'emptyMessage' => null,
    'emptyIcon' => 'user-group',
])

@php
    use App\Services\DataTable\View\DataTableViewData;

    // If viewData is provided, use it; otherwise create from props (backward compatibility)
    if (!$viewData) {
        $viewData = new DataTableViewData(
            rows: $rows,
            headers: $headers,
            columns: $columns,
            actionsPerRow: $actionsPerRow,
            bulkActions: [],
            filters: [],
            selected: $selected,
            rowClick: $rowClick,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
            showBulk: $showBulk,
            selectPage: $selectPage,
            selectAll: $selectAll,
            showSearch: false,
            searchPlaceholder: null,
            paginator: null,
            emptyMessage: $emptyMessage,
            emptyIcon: $emptyIcon,
            class: $class,
            openRowActionModal: null,
            openRowActionUuid: null,
            openBulkActionModal: null,
        );
    }
@endphp

<div class="overflow-x-auto {{ $viewData->getClass() }}">
    <table class="table">
        <x-table.header :view-data="$viewData"></x-table.header>

        <x-table.body>
            @forelse ($viewData->getRows() as $row)
                @php
                    $rowData = $viewData->processRow($row, $loop->index);
                @endphp
                <tr wire:key="row-{{ $rowData['uuid'] ?? $rowData['index'] }}" {!! $rowData['rowClickAttr'] !!}
                    {!! $rowData['rowClassAttr'] !!}>
                    {{-- Bulk Selection Checkbox --}}
                    @if ($viewData->isShowBulk() && $rowData['uuid'])
                        <td wire:click.stop>
                            <input type="checkbox" wire:model.live="selected" value="{{ $rowData['uuid'] }}"
                                class="checkbox checkbox-sm" />
                        </td>
                    @endif

                    {{-- Data Columns --}}
                    @foreach ($viewData->getColumns() as $column)
                        @php
                            $columnData = $viewData->processColumn($column, $row);
                        @endphp

                        @if (!$columnData['hidden'])
                            <td {!! $columnData['cellClassAttr'] !!}>
                                @if ($columnData['hasCustomRender'])
                                    {{-- Custom render will be handled server-side in Livewire --}}
                                    {{ $columnData['value'] ?? '' }}
                                @elseif ($columnData['componentName'])
                                    <x-dynamic-component :component="$columnData['componentName']" :value="$columnData['value']"
                                        :name="$row['name'] ?? 'User'"></x-dynamic-component>
                                @else
                                    {{ $columnData['value'] ?? '' }}
                                @endif
                            </td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <x-table.empty :columns-count="$viewData->getColumnsCount()" :message="$viewData->getEmptyMessage()" :icon="$viewData->getEmptyIcon()"></x-table.empty>
            @endforelse
        </x-table.body>
    </table>
</div>
