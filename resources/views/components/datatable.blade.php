<div>
    {{-- Alpine.js DataTable Component --}}
    {{-- NOTE: $wire is automatically available in Alpine context, do NOT pass as parameter --}}
    <div x-data="dataTable(@js(['pageUuids' => $this->rows->pluck('uuid')->toArray()]))"
         @datatable-updated.window="
             pageUuids = $event.detail.pageUuids || [];
             selectPage = pageUuids.length > 0 && pageUuids.every(uuid => selected.includes(uuid));
         ">
        {{-- Header with Search and Filters --}}
        <div class="mb-6 flex flex-col gap-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Search --}}
                <div class="flex-1 max-w-md">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="{{ __('ui.table.search_placeholder') }}"
                        class="input input-md w-full"
                    >
                </div>

                {{-- Filter Toggle Button --}}
                <button
                    @click="toggleFilters()"
                    type="button"
                    class="btn btn-ghost btn-md"
                >
                    <x-ui.icon name="funnel" size="sm"></x-ui.icon>
                    {{ __('ui.table.filters') }}
                    @if (count($this->getActiveFilters()) > 0)
                        <span class="badge badge-primary badge-sm">{{ count($this->getActiveFilters()) }}</span>
                    @endif
                </button>
            </div>

            {{-- Active Filters Badges --}}
            @if (count($this->getActiveFilters()) > 0)
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm text-base-content/70">{{ __('ui.table.active_filters') }}:</span>
                    @foreach ($this->getActiveFilters() as $filter)
                        <div class="badge badge-lg gap-2">
                            <span class="font-medium">{{ $filter['label'] }}:</span>
                            <span>{{ $filter['valueLabel'] }}</span>
                            <button
                                wire:click="removeFilter('{{ $filter['key'] }}')"
                                type="button"
                                class="btn btn-ghost btn-circle btn-xs"
                            >
                                <x-ui.icon name="x-mark" size="xs"></x-ui.icon>
                            </button>
                        </div>
                    @endforeach
                    <button
                        wire:click="clearFilters"
                        type="button"
                        class="btn btn-ghost btn-sm"
                    >
                        {{ __('ui.actions.clear_all') }}
                    </button>
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
                            <div>
                                <label class="label">
                                    <span class="label-text">{{ $filter['label'] }}</span>
                                </label>
                                @if ($filter['type'] === 'select')
                                    <select
                                        wire:model.live="filters.{{ $filter['key'] }}"
                                        class="select select-md w-full"
                                    >
                                        <option value="">{{ $filter['placeholder'] ?? __('ui.table.select_option') }}</option>
                                        @foreach ($filter['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="card-actions justify-end mt-4">
                        <button
                            wire:click="clearFilters"
                            type="button"
                            class="btn btn-ghost btn-sm"
                        >
                            {{ __('ui.actions.clear_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div x-show="hasSelection" class="mb-4 flex items-center gap-4">
            <span class="text-sm font-medium" x-text="`${selectedCount} {{ __('ui.table.selected') }}`"></span>

            @foreach ($this->getBulkActions() as $action)
                <button
                    @if($action['confirm'])
                        @click="executeActionWithConfirmation('{{ $action['key'] }}', null, true)"
                    @else
                        wire:click="executeBulkAction('{{ $action['key'] }}')"
                    @endif
                    type="button"
                    @class([
                        'btn',
                        "btn-{$action['variant']}",
                        "btn-{$action['color']}" => $action['color'] !== null,
                        'btn-sm',
                    ])
                >
                    @if ($action['icon'])
                        <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                    @endif
                    {{ $action['label'] }}
                </button>
            @endforeach

            <button
                @click="clearSelection()"
                type="button"
                class="btn btn-ghost btn-sm"
            >
                {{ __('ui.actions.clear_selection') }}
            </button>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        {{-- Select All Checkbox --}}
                        <th class="w-12">
                            <input
                                type="checkbox"
                                @click="toggleSelectPage()"
                                :checked="selectPage"
                                class="checkbox checkbox-sm"
                            >
                        </th>

                {{-- Column Headers --}}
                @foreach ($this->getColumns() as $column)
                    <th
                        @if ($column['sortable'])
                            wire:click="sortBy('{{ $column['field'] }}')"
                            class="cursor-pointer select-none hover:bg-base-200"
                        @endif
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ $column['label'] }}</span>
                            @if ($column['sortable'] && $sortBy === $column['field'])
                                <x-ui.icon
                                    :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'"
                                    size="xs"
                                ></x-ui.icon>
                            @endif
                        </div>
                    </th>
                @endforeach

                        {{-- Actions Column --}}
                        <th class="w-24">{{ __('ui.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $row)
                        <tr
                            wire:key="row-{{ $row->uuid }}"
                            @click="handleRowClick('{{ $row->uuid }}')"
                            @mouseenter="setHoveredRow('{{ $row->uuid }}')"
                            @mouseleave="setHoveredRow(null)"
                            :class="{ 'bg-base-200': isSelected('{{ $row->uuid }}') }"
                            class="cursor-pointer hover:bg-base-200 transition-colors"
                        >
                            {{-- Selection Checkbox --}}
                            <td @click.stop>
                                <input
                                    type="checkbox"
                                    :checked="isSelected('{{ $row->uuid }}')"
                                    @click="toggleRow('{{ $row->uuid }}')"
                                    class="checkbox checkbox-sm"
                                >
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
                                        <button type="button" class="btn btn-ghost btn-sm btn-square">
                                            <x-ui.icon name="ellipsis-vertical" size="sm"></x-ui.icon>
                                        </button>
                                    </x-slot:trigger>

                                    @foreach ($this->getRowActionsForRow($row) as $action)
                                        @if ($action['hasRoute'])
                                            <li>
                                                <a
                                                    href="{{ $action['route'] }}"
                                                    wire:navigate
                                                    @class(['flex items-center gap-2'])
                                                >
                                                    @if ($action['icon'])
                                                        <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                                                    @endif
                                                    {{ $action['label'] }}
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <button
                                                    @if($action['confirm'])
                                                        @click="executeActionWithConfirmation('{{ $action['key'] }}', '{{ $row->uuid }}', false)"
                                                    @else
                                                        wire:click="executeAction('{{ $action['key'] }}', '{{ $row->uuid }}')"
                                                    @endif
                                                    type="button"
                                                    @class(['flex items-center gap-2 w-full'])
                                                >
                                                    @if ($action['icon'])
                                                        <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                                                    @endif
                                                    {{ $action['label'] }}
                                                </button>
                                            </li>
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
        <div class="mt-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
            {{-- Per Page Selector --}}
            <div class="flex items-center gap-2">
                <label class="label-text">{{ __('ui.table.per_page') }}:</label>
                <select
                    wire:model.live="perPage"
                    class="select select-sm"
                >
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            {{-- Pagination Links --}}
            <div class="flex-1 flex justify-center">
                {{ $this->rows->links() }}
            </div>

            {{-- Results Info --}}
            <div class="text-sm text-base-content/70">
                {{ __('ui.table.showing_results', [
                    'from' => $this->rows->firstItem() ?? 0,
                    'to' => $this->rows->lastItem() ?? 0,
                    'total' => $this->rows->total(),
                ]) }}
            </div>
        </div>

        {{-- Confirmation Modal --}}
        <dialog
            x-ref="confirmModal"
            x-show="activeModal === 'confirm-action-modal'"
            @click.self="cancelAction()"
            class="modal"
            :class="{ 'modal-open': activeModal === 'confirm-action-modal' }"
        >
            <div class="modal-box" @click.stop>
                <h3 class="font-bold text-lg mb-4" x-text="confirmationConfig?.title || '{{ __('ui.actions.confirm') }}'"></h3>

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
                    <button
                        @click="cancelAction()"
                        type="button"
                        class="btn btn-ghost"
                    >
                        <span x-text="confirmationConfig?.cancelText || '{{ __('ui.actions.cancel') }}'"></span>
                    </button>
                    <button
                        @click="confirmAction()"
                        type="button"
                        class="btn btn-error"
                    >
                        <span x-text="confirmationConfig?.confirmText || '{{ __('ui.actions.confirm') }}'"></span>
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop" @click="cancelAction()">
                <button type="button">close</button>
            </form>
        </dialog>
    </div>{{-- End Alpine.js DataTable Component --}}
</div>
