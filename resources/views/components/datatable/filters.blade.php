{{-- Header with Search and Filters --}}
<div class="mb-6 flex flex-col gap-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Search --}}
        <div class="flex-1 max-w-md" wire:ignore>
            <x-ui.search wire:model.live.debounce.300ms="search" type="text"
                placeholder="{{ __('ui.table.search_placeholder') }}"></x-ui.search>
        </div>

        {{-- Bulk Actions Dropdown --}}
        @if ($this->hasSelection)
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-base-content/70">{{ $this->selectedCount }} {{ __('ui.table.selected') }}</span>
                
                <x-ui.dropdown placement="bottom-start" menu menuSize="sm" teleport>
                    <x-slot:trigger>
                        <x-ui.button type="button" style="outline" size="sm" class="gap-2">
                            {{ __('ui.table.bulk_actions') }}
                            <x-ui.icon name="chevron-down" size="sm"></x-ui.icon>
                        </x-ui.button>
                    </x-slot:trigger>

                    @foreach ($this->getBulkActions() as $action)
                        @if ($action['confirm'])
                            <button @click="executeActionWithConfirmation('{{ $action['key'] }}', null, true)"
                                type="button" class="flex items-center gap-2 w-full text-left">
                                @if ($action['icon'])
                                    <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                                @endif
                                {{ $action['label'] }}
                            </button>
                        @else
                            <button wire:click="executeBulkAction('{{ $action['key'] }}')" type="button"
                                class="flex items-center gap-2 w-full text-left">
                                @if ($action['icon'])
                                    <x-ui.icon :name="$action['icon']" size="sm"></x-ui.icon>
                                @endif
                                {{ $action['label'] }}
                            </button>
                        @endif
                    @endforeach
                </x-ui.dropdown>

                <x-ui.button wire:click="clearSelection()" type="button" style="ghost" size="sm" class="text-error hover:bg-error/10">
                    <x-ui.icon name="x-mark" size="sm"></x-ui.icon>
                    {{ __('ui.actions.clear_selection') }}
                </x-ui.button>
            </div>
        @else
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
            </div>
        @endif
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
