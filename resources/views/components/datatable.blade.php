<div>
    {{-- Alpine.js DataTable Component --}}
    {{-- NOTE: $wire is automatically available in Alpine context, do NOT pass as parameter --}}
    <div x-data="dataTable">
        {{-- Header with Search and Filters --}}
        <div class="mb-6 flex flex-col gap-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Search --}}
                <div class="flex-1 max-w-md" wire:ignore>
                    <x-ui.search wire:model.live.debounce.300ms="search" type="text"
                        placeholder="{{ __('ui.table.search_placeholder') }}"></x-ui.search>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Filter Toggle Button --}}
                    <x-ui.button @click="toggleFilters()" type="button" style="ghost" size="md">
                        <x-ui.icon name="funnel" size="sm"></x-ui.icon>
                        {{ __('ui.table.filters') }}
                        @if (count($this->getActiveFilters()) > 0)
                            <x-ui.badge variant="primary"
                                size="sm">{{ count($this->getActiveFilters()) }}</x-ui.badge>
                        @endif
                    </x-ui.button>

                    {{-- Share Button --}}
                    <x-ui.share-button :url="$this->getShareUrl()" size="md" style="ghost"></x-ui.share-button>
                </div>
            </div>

            {{-- Active Filters Badges --}}
            @if (count($this->getActiveFilters()) > 0)
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm text-base-content/70">{{ __('ui.table.active_filters') }}:</span>
                    @foreach ($this->getActiveFilters() as $filter)
                        <x-ui.badge size="sm" variant="secondary" class="gap-1">
                            <span class="font-medium">{{ $filter['label'] }}:</span>
                            <span>{{ $filter['valueLabel'] }}</span>
                            <x-ui.button wire:click="removeFilter('{{ $filter['key'] }}')" type="button"
                                variant="ghost" size="xs" circle>
                                <x-ui.icon name="x-mark" size="xs"></x-ui.icon>
                            </x-ui.button>
                        </x-ui.badge>
                    @endforeach
                    <x-ui.button wire:click="clearFilters" type="button" variant="link" size="sm">
                        {{ __('ui.actions.clear_all') }}
                    </x-ui.button>
                </div>
            @endif
        </div>

        {{-- Filters Panel --}}
        <div x-show="openFilters" x-collapse class="mb-6">
            <div class="card card-border">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">{{ __('ui.table.filters') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($this->getFilters() as $filter)
                            @if ($filter['type'] === 'select')
                                <x-ui.select wire:model.live="filters.{{ $filter['key'] }}" :label="$filter['label']"
                                    class="select-md" :options="$filter['options']">
                                </x-ui.select>
                            @endif
                        @endforeach
                    </div>

                    <div class="card-actions justify-end mt-4">
                        <x-ui.button wire:click="clearFilters" type="button" style="ghost" size="sm">
                            {{ __('ui.actions.clear_filters') }}
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        @if ($this->hasSelection)
            <div class="mb-4 flex items-center gap-4">
                <span class="text-sm font-medium">{{ $this->selectedCount }} {{ __('ui.table.selected') }}</span>

                @foreach ($this->getBulkActions() as $action)
                    @if ($action['confirm'])
                        <x-ui.button @click="executeActionWithConfirmation('{{ $action['key'] }}', null, true)"
                            type="button" :style="$action['variant'] ?? 'solid'" :color="$action['color'] ?? null" size="sm">
                            @if ($action['icon'])
                                <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                            @endif
                            {{ $action['label'] }}
                        </x-ui.button>
                    @else
                        <x-ui.button wire:click="executeBulkAction('{{ $action['key'] }}')" type="button"
                            :style="$action['variant'] ?? 'solid'" :color="$action['color'] ?? null" size="sm">
                            @if ($action['icon'])
                                <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                            @endif
                            {{ $action['label'] }}
                        </x-ui.button>
                    @endif
                @endforeach

                <x-ui.button wire:click="clearSelection()" type="button" style="ghost" size="sm">
                    {{ __('ui.actions.clear_selection') }}
                </x-ui.button>
            </div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        {{-- Select All Checkbox --}}
                        <th class="w-12">
                            <input type="checkbox" wire:click="toggleSelectAll()" @checked($this->isAllSelected)
                                class="checkbox checkbox-sm">
                        </th>

                        {{-- Column Headers --}}
                        @foreach ($this->getColumns() as $column)
                            @if ($column['sortable'])
                                <th wire:click="sort('{{ $column['field'] }}')"
                                    class="cursor-pointer select-none hover:bg-base-200">
                                    <div class="flex items-center gap-2 justify-between">
                                        <span>{{ $column['label'] }}</span>
                                        @if ($sortBy === $column['field'])
                                            <x-ui.icon :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" size="xs"></x-ui.icon>
                                        @endif
                                    </div>
                                </th>
                            @else
                                <th>
                                    <div class="flex items-center gap-2">
                                        <span>{{ $column['label'] }}</span>
                                    </div>
                                </th>
                            @endif
                        @endforeach

                        {{-- Actions Column --}}
                        <th class="w-24">{{ __('ui.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $row)
                        <tr wire:key="row-{{ $row->uuid }}" wire:click="rowClicked('{{ $row->uuid }}')"
                            @mouseenter="setHoveredRow('{{ $row->uuid }}')" @mouseleave="setHoveredRow(null)"
                            @class([
                                'bg-base-200' => $this->isSelected($row->uuid),
                                'cursor-pointer hover:bg-base-200 transition-colors',
                            ])>
                            {{-- Selection Checkbox --}}
                            <td @click.stop>
                                <input type="checkbox" wire:click.stop="toggleRow('{{ $row->uuid }}')"
                                    @checked($this->isSelected($row->uuid)) class="checkbox checkbox-sm">
                            </td>

                            {{-- Data Columns --}}
                            @foreach ($this->getColumns() as $column)
                                <td class="{{ $column['class'] }}">
                                    {!! $this->renderColumn($column, $row) !!}
                                </td>
                            @endforeach

                            {{-- Actions Dropdown --}}
                            <td @click.stop>
                                <x-ui.dropdown placement="end" menu menuSize="sm">
                                    <x-slot:trigger>
                                        <x-ui.button type="button" style="ghost" size="sm" class="btn-square">
                                            <x-ui.icon name="ellipsis-vertical" size="sm"></x-ui.icon>
                                        </x-ui.button>
                                    </x-slot:trigger>

                                    @foreach ($this->getRowActionsForRow($row) as $action)
                                        @if ($action['hasRoute'])
                                            <li>
                                                <a href="{{ $action['route'] }}" wire:navigate
                                                    class="flex items-center gap-2">
                                                    @if ($action['icon'])
                                                        <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                                                    @endif
                                                    {{ $action['label'] }}
                                                </a>
                                            </li>
                                        @else
                                            @if ($action['confirm'])
                                                <li>
                                                    <x-ui.button
                                                        @click="executeActionWithConfirmation('{{ $action['key'] }}', '{{ $row->uuid }}', false)"
                                                        type="button" class="flex items-center gap-2 w-full">
                                                        @if ($action['icon'])
                                                            <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                                                        @endif
                                                        {{ $action['label'] }}
                                                    </x-ui.button>
                                                </li>
                                            @else
                                                <li>
                                                    <x-ui.button
                                                        wire:click="executeAction('{{ $action['key'] }}', '{{ $row->uuid }}')"
                                                        type="button" class="flex items-center gap-2 w-full">
                                                        @if ($action['icon'])
                                                            <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                                                        @endif
                                                        {{ $action['label'] }}
                                                    </x-ui.button>
                                                </li>
                                            @endif
                                        @endif
                                    @endforeach
                                </x-ui.dropdown>
                            </td>
                        </tr>
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



        {{-- Confirmation Modal --}}
        <dialog x-ref="confirmModal" x-show="activeModal === 'confirm-action-modal'" @click.self="cancelAction()"
            class="modal" :class="{ 'modal-open': activeModal === 'confirm-action-modal' }">
            <div class="modal-box" @click.stop>
                <h3 class="font-bold text-lg mb-4"
                    x-text="confirmationConfig?.title || '{{ __('ui.actions.confirm') }}'">
                </h3>

                <template x-if="confirmationConfig?.type === 'message'">
                    <p class="py-4" x-text="confirmationConfig.message"></p>
                </template>

                <template x-if="confirmationConfig?.type === 'config'">
                    <div class="py-4">
                        <p x-text="confirmationConfig.content"></p>
                    </div>
                </template>

                <template x-if="confirmationConfig?.type === 'view'">
                    <div class="py-4">
                        {{-- Custom view content would be rendered here --}}
                        <p>Custom confirmation view</p>
                    </div>
                </template>

                <div class="modal-action">
                    <x-ui.button @click="cancelAction()" type="button" style="ghost">
                        <span x-text="confirmationConfig?.cancelText || '{{ __('ui.actions.cancel') }}'"></span>
                    </x-ui.button>
                    <x-ui.button @click="confirmAction()" type="button" color="error">
                        <span x-text="confirmationConfig?.confirmText || '{{ __('ui.actions.confirm') }}'"></span>
                    </x-ui.button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop" @click="cancelAction()">
                <button type="button">close</button>
            </form>
        </dialog>
    </div>{{-- End Alpine.js DataTable Component --}}
</div>
