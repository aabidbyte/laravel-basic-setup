{{--
    Table Component
    All props are handled by the Table component class (App\View\Components\Table)
    All methods are available directly from the component class
--}}

<div class="overflow-x-auto {{ $getClass() }}">
    <table class="table">
        <x-table.header
            :columns="$headers"
            :sort-by="$sortBy"
            :sort-direction="$sortDirection"
            :show-bulk="$showBulk"
            :select-page="$selectPage"
            :select-all="$selectAll"
            :show-actions="$hasActionsPerRow()"
        ></x-table.header>

        <x-table.body>
            @forelse ($getRows() as $row)
                @php
                    $rowData = $processRow($row, $loop->index);
                @endphp
                <tr wire:key="row-{{ $rowData['uuid'] ?? $rowData['index'] }}" {!! $rowData['rowClickAttr'] !!}
                    {!! $rowData['rowClassAttr'] !!}>
                    {{-- Bulk Selection Checkbox --}}
                    @if ($isShowBulk() && $rowData['uuid'])
                        <td wire:click.stop>
                            <input type="checkbox" wire:model.live="selected" value="{{ $rowData['uuid'] }}"
                                class="checkbox checkbox-sm" />
                        </td>
                    @endif

                    {{-- Data Columns --}}
                    @foreach ($getColumns() as $column)
                        @php
                            $columnData = $processColumn($column, $row);
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
                <x-table.empty :columns-count="$getColumnsCount()" :message="$getEmptyMessage()" :icon="$getEmptyIcon()"></x-table.empty>
            @endforelse
        </x-table.body>
    </table>
</div>
