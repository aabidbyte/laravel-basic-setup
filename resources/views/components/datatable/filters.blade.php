@use('App\Enums\DataTable\DataTableFilterType')

{{-- Header with Search and Filters --}}
<div class="z-31 bg-base-100 sticky top-0 flex flex-col gap-4 py-2 sm:flex-row sm:items-center sm:justify-between">
    {{-- Search --}}
    <div class="max-w-md flex-1"
         wire:ignore>
        <x-ui.search wire:model.live.debounce.300ms="search"
                     type="text"
                     placeholder="{{ __('table.search_placeholder') }}"></x-ui.search>
    </div>

    {{-- Bulk Actions Dropdown --}}
    @if ($this->hasSelection)
        <div class="flex items-center gap-4">
            <span class="text-base-content/70 text-sm font-medium">{{ $this->selectedCount }}
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

                @foreach ($this->getBulkActions() as $action)
                    @php
                        $actionClick = $action['confirm']
                            ? "executeActionWithConfirmation('{$action['key']}', null, true)"
                            : null;

                        $wireClick = !$action['confirm'] ? "executeBulkAction('{$action['key']}')" : null;
                    @endphp

                    <x-ui.button wire:key="bulk-action-{{ $action['key'] }}"
                                 type="button"
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
    @elseif ($this->hasFilters())
        {{-- Filter Toggle Button - only render if filters are defined --}}
        <div class="hidden flex-row-reverse items-center gap-2 py-2 md:flex">
            <x-ui.button @click="toggleFilters()"
                         type="button"
                         variant="outline"
                         size="md">
                <x-ui.icon name="funnel"
                           size="sm"></x-ui.icon>
                {{ __('table.filters') }}
                @if (count($this->getActiveFilters()) > 0)
                    <x-ui.badge color="primary"
                                size="sm">{{ count($this->getActiveFilters()) }}</x-ui.badge>
                @endif
            </x-ui.button>
        </div>
    @endif
</div>

@if ($this->hasFilters() && !$this->hasSelection)
    {{-- Filter Toggle Button - only render if filters are defined --}}
    <div class="flex flex-row-reverse items-center gap-2 py-2 md:hidden">
        <x-ui.button @click="toggleFilters()"
                     type="button"
                     variant="outline"
                     size="md">
            <x-ui.icon name="funnel"
                       size="sm"></x-ui.icon>
            {{ __('table.filters') }}
            @if (count($this->getActiveFilters()) > 0)
                <x-ui.badge color="primary"
                            size="sm">{{ count($this->getActiveFilters()) }}</x-ui.badge>
            @endif
        </x-ui.button>
    </div>
@endif
{{-- Active Filters Badges --}}
@if (count($this->getActiveFilters()) > 0)
    <div class="grid grid-cols-12 place-items-center gap-2">
        <div class="col-span-2">
            <span class="text-base-content/70 text-sm">{{ __('table.active_filters') }}:</span>
        </div>
        <div class="col-span-8">
            @foreach ($this->getActiveFilters() as $filter)
                <x-ui.badge wire:key="active-filter-{{ $filter['key'] }}"
                            size="sm"
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
        </div>
        <div class="col-span-2">
            <x-ui.button wire:click="clearFilters"
                         type="button"
                         variant="outline"
                         color="error"
                         size="md">
                {{ __('actions.clear_all') }}
            </x-ui.button>
        </div>
    </div>
@endif
{{-- Filters Panel - only render if filters are defined --}}
@if ($this->hasFilters())
    <div x-show="openFilters"
         x-collapse
         class="mb-6">
        <div class="card card-border">
            <div class="card-body">
                <h3 class="card-title mb-4 text-lg">{{ __('table.filters') }}</h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach ($this->getFilters() as $filter)
                        @if ($filter['type'] === DataTableFilterType::SELECT)
                            <x-ui.select wire:key="filter-input-{{ $filter['key'] }}"
                                         wire:model.live="filters.{{ $filter['key'] }}"
                                         :label="$filter['label']"
                                         class="select-md"
                                         :options="$filter['options']">
                            </x-ui.select>
                        @elseif ($filter['type'] === DataTableFilterType::DATE_RANGE)
                            <x-ui.date-range wire:key="filter-input-{{ $filter['key'] }}"
                                             :label="$filter['label']"
                                             wire:model.from.live="filters.{{ $filter['key'] }}.from"
                                             wire:model.to.live="filters.{{ $filter['key'] }}.to" />
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
