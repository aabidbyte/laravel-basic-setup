@use('App\Enums\DataTable\DataTableFilterType')

{{-- Header with Search and Filters --}}
<div class="mb-6 flex flex-col gap-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Search --}}
        <div class="max-w-md flex-1"
             wire:ignore>
            <x-ui.search wire:model.live.debounce.300ms="search"
                         type="text"
                         placeholder="{{ __('table.search_placeholder') }}"></x-ui.search>
        </div>

        {{-- Bulk Actions Dropdown --}}
        @if ($datatable->hasSelection)
            <div class="flex items-center gap-4">
                <span class="text-base-content/70 text-sm font-medium">{{ $datatable->selectedCount }}
                    {{ __('table.selected') }}</span>

                <x-ui.dropdown placement="bottom-start"
                               menu
                               menuSize="sm"
                               teleport>
                    <x-slot:trigger>
                        <x-ui.button type="button"
                                     variant="outline"
                                     size="sm"
                                     class="gap-2">
                            {{ __('table.bulk_actions') }}
                            <x-ui.icon name="chevron-down"
                                       size="sm"></x-ui.icon>
                        </x-ui.button>
                    </x-slot:trigger>

                    @foreach ($datatable->getBulkActions() as $action)
                        @php
                            $actionClick = $action['confirm']
                                ? "executeActionWithConfirmation('{$action['key']}', null, true)"
                                : null;

                            $wireClick = !$action['confirm'] ? "executeBulkAction('{$action['key']}')" : null;
                        @endphp

                        <x-ui.button type="button"
                                     :@click="$actionClick ?: null"
                                     :wire:click="$wireClick ?: null"
                                     :variant="$action['variant'] ?? 'ghost'"
                                     :color="$action['color'] ?? null"
                                     size="sm"
                                     class="justify-start">
                            @if ($action['icon'])
                                <x-ui.icon :name="$action['icon']"
                                           size="sm"></x-ui.icon>
                            @endif
                            {{ $action['label'] }}
                        </x-ui.button>
                    @endforeach
                </x-ui.dropdown>

                <x-ui.button wire:click="clearSelection()"
                             type="button"
                             variant="ghost"
                             color="error"
                             size="sm">
                    <x-ui.icon name="x-mark"
                               size="sm"></x-ui.icon>
                    {{ __('actions.clear_selection') }}
                </x-ui.button>
            </div>
        @elseif ($datatable->hasFilters())
            {{-- Filter Toggle Button - only render if filters are defined --}}
            <div class="flex items-center gap-2">
                <x-ui.button @click="toggleFilters()"
                             type="button"
                             variant="ghost"
                             size="md">
                    <x-ui.icon name="funnel"
                               size="sm"></x-ui.icon>
                    {{ __('table.filters') }}
                    @if (count($datatable->getActiveFilters()) > 0)
                        <x-ui.badge color="primary"
                                    size="sm">{{ count($datatable->getActiveFilters()) }}</x-ui.badge>
                    @endif
                </x-ui.button>
            </div>
        @endif
    </div>

    {{-- Active Filters Badges --}}
    @if (count($datatable->getActiveFilters()) > 0)
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-base-content/70 text-sm">{{ __('table.active_filters') }}:</span>
            @foreach ($datatable->getActiveFilters() as $filter)
                <x-ui.badge size="sm"
                            color="secondary"
                            class="gap-1">
                    <span class="font-medium">{{ $filter['label'] }}:</span>
                    <span>{{ $filter['valueLabel'] }}</span>
                    <x-ui.button wire:click="removeFilter('{{ $filter['key'] }}')"
                                 type="button"
                                 variant="ghost"
                                 size="xs"
                                 circle>
                        <x-ui.icon name="x-mark"
                                   size="xs"></x-ui.icon>
                    </x-ui.button>
                </x-ui.badge>
            @endforeach
            <x-ui.button wire:click="clearFilters"
                         type="button"
                         variant="link"
                         size="sm">
                {{ __('actions.clear_all') }}
            </x-ui.button>
        </div>
    @endif
</div>

{{-- Filters Panel - only render if filters are defined --}}
@if ($datatable->hasFilters())
    <div x-show="openFilters"
         x-collapse
         class="mb-6">
        <div class="card card-border">
            <div class="card-body">
                <h3 class="card-title mb-4 text-lg">{{ __('table.filters') }}</h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach ($datatable->getFilters() as $filter)
                        @if ($filter['type'] === DataTableFilterType::SELECT)
                            <x-ui.select wire:model.live="filters.{{ $filter['key'] }}"
                                         :label="$filter['label']"
                                         class="select-md"
                                         :options="$filter['options']">
                            </x-ui.select>
                        @elseif ($filter['type'] === DataTableFilterType::DATE_RANGE)
                             <x-ui.date-range
                                 :label="$filter['label']"
                                 wire:model.from.live="filters.{{ $filter['key'] }}.from"
                                 wire:model.to.live="filters.{{ $filter['key'] }}.to"
                             />
                        @endif
                    @endforeach
                </div>

                <div class="card-actions mt-4 justify-end">
                    <x-ui.button wire:click="clearFilters"
                                 type="button"
                                 variant="ghost"
                                 size="sm">
                        {{ __('actions.clear_filters') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
@endif
