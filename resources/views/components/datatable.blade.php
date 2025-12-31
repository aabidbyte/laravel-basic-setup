<div>
    <div x-data="dataTable('{{ $this->getId() }}')"
        @datatable:action-confirmed:{{ $this->getId() }}.window="confirmAction($event.detail)"
        @datatable:action-cancelled:{{ $this->getId() }}.window="cancelAction()"
        @datatable:scroll-to-top:{{ $this->getId() }}.window="$el.scrollIntoView({ behavior: 'smooth' })"
        @datatable:clean-url:{{ $this->getId() }}.window="window.history.replaceState({}, document.title, window.location.pathname)">

        {!! $this->renderFilters() !!}
        {{ $this->rows->links('components.datatable.pagination') }}

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                {!! $this->renderTableHeader() !!}

                <tbody>
                    @forelse ($this->rows as $row)
                        {!! $this->renderTableRow($row) !!}
                    @empty
                        <tr>
                            <td colspan="{{ count($this->getColumns()) + 2 }}" class="text-center py-12">
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

        {{-- Pagination --}}
        {{ $this->rows->links('components.datatable.pagination') }}

        {{-- Dynamic Action Modal --}}
        <div x-data="{ modalIsOpen: false }"
            @datatable:open-modal:{{ $this->getId() }}.window="modalIsOpen = true"
            @datatable:close-modal:{{ $this->getId() }}.window="modalIsOpen = false">
            <x-ui.base-modal open-state="modalIsOpen" use-parent-state="true" :title="__('ui.table.action_modal_title')"
                on-close="$wire.closeActionModal()">
                @if ($modalComponent)
                    @if ($modalType === 'blade')
                        @include($modalComponent, $modalProps)
                    @else
                        <livewire:is :component="$modalComponent" v-bind="$modalProps" :key="'modal-' . $modalComponent" />
                    @endif
                @endif
            </x-ui.base-modal>
        </div>
    </div>{{-- End Alpine.js DataTable Component --}}
</div>
