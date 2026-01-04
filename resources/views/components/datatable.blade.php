{{-- DataTable Component Template --}}
{{-- Optimized for minimal Livewire payloads with loading states --}}
@php
    $datatableId = $this->getId();
    $rows = $this->rows;
    $columns = $this->getColumns();
    $countColumns = count($columns);
@endphp

<div>
    <div x-data="dataTable('{{ $datatableId }}')">

        {{-- Filters (search, bulk actions, filter panel) --}}
        {!! $this->renderFilters() !!}

        {{-- Top Pagination --}}
        {{ $rows->links('components.datatable.pagination') }}

        {{-- Table with Loading Overlay --}}
        <div class="relative overflow-x-auto">
            {{-- Loading Overlay --}}
            <div wire:loading.delay.shortest 
                 wire:target="sort, search, filters, perPage, gotoPage, previousPage, nextPage, toggleSelectAll, selected"
                 class="absolute inset-0 bg-base-100/50 z-50 flex items-center justify-center backdrop-blur-[1px]">
                <x-ui.loading size="md" :centered="false"></x-ui.loading>
            </div>

            <table class="table table-zebra" wire:loading.class="opacity-50" 
                   wire:target="sort, search, filters, perPage, gotoPage, previousPage, nextPage">
                {{-- Table Header --}}
                {!! $this->renderTableHeader() !!}

                {{-- Table Body --}}
                <tbody>
                    @forelse ($rows as $row)
                        {!! $this->renderTableRow($row) !!}
                    @empty
                        <tr wire:key="empty-row-{{ $datatableId }}">
                            <td colspan="{{ $countColumns + 2 }}" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-base-content/50">
                                    <x-ui.icon name="users" size="lg"></x-ui.icon>
                                    <p>{{ __('ui.table.no_results') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bottom Pagination --}}
        {{ $rows->links('components.datatable.pagination') }}

        {{-- Modal is now global: see components/datatable/action-modal.blade.php --}}
    </div>
</div>

