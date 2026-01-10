{{-- DataTable Component Template --}}
{{-- Optimized for minimal Livewire payloads with loading states --}}
@php
    $datatableId = $this->getId();
    $rows = $this->rows;
    $columns = $this->getColumns();
    $countColumns = count($columns);

    // Calculate extra columns for checkbox and actions
    $extraColumns = 0;
    if ($this->hasBulkActions()) {
        $extraColumns++;
    }
    if ($this->hasRowActions()) {
        $extraColumns++;
    }
@endphp

<div>
    <div x-data="dataTable('{{ $datatableId }}')">

        {{-- Filters (search, bulk actions, filter panel) --}}
        {!! $this->renderFilters() !!}

        {{-- Top Pagination --}}
        {{ $rows->links('components.datatable.pagination') }}

        {{-- Table with Loading Overlay --}}
        <div class="relative overflow-x-auto">
            {{-- Loading Overlay - uses wire:loading.flex to ensure display:flex when shown --}}
            <div wire:loading.flex.delay.shortest
                 wire:target="sort, search, filters, perPage, gotoPage, previousPage, nextPage, toggleSelectAll, selected"
                 class="bg-base-100/50 absolute inset-0 z-50 hidden items-center justify-center backdrop-blur-[1px]">
                <x-ui.loading size="md"
                              :centered="false"></x-ui.loading>
            </div>

            <table class="table-zebra table"
                   wire:loading.class="opacity-50"
                   wire:target="sort, search, filters, perPage, gotoPage, previousPage, nextPage">
                {{-- Table Header --}}
                {!! $this->renderTableHeader() !!}

                <tbody x-ref="tbody">
                    @forelse ($rows->take($this->visibleRows) as $row)
                        {!! $this->renderTableRow($row) !!}
                    @empty
                        <tr wire:key="empty-row-{{ $datatableId }}">
                            <td colspan="{{ $countColumns + $extraColumns }}"
                                class="py-12 text-center">
                                <div class="text-base-content/50 flex flex-col items-center gap-2">
                                    <x-ui.icon name="users"
                                               size="lg"></x-ui.icon>
                                    <p>{{ __('table.no_results') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Load More Trigger --}}
            @if ($rows->count() > $this->visibleRows)
                <div x-data="infiniteScroll"
                     class="flex h-8 items-center justify-center p-4">
                    <x-ui.loading size="sm" />
                </div>
            @endif
        </div>

        {{-- Bottom Pagination --}}
        {{ $rows->links('components.datatable.pagination') }}

        {{-- Modal is now global: see components/datatable/action-modal.blade.php --}}
    </div>
</div>
