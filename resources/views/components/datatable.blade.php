{{--
    DataTable Component
    All props are handled by the Datatable component class (App\View\Components\Datatable)
    All methods are available directly from the component class
--}}

<div class="space-y-4 {{ $getClass() }}">
    {{-- Search Bar (by default) --}}
    @if ($isShowSearch())
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-2">
                <x-ui.input type="text" wire:model.live.debounce.300ms="search" :placeholder="$getSearchPlaceholder()"
                    class="max-w-xs"></x-ui.input>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    @if ($hasFilters())
        <div class="flex flex-wrap gap-4">
            @foreach ($getFilters() as $filter)
                @php
                    $processedFilter = $processFilter($filter);
                    $componentName = $processedFilter['component'];
                    $safeAttributes = $processedFilter['safeAttributes'];
                @endphp

                @if ($componentName)
                    <x-dynamic-component :component="$componentName" :key="$filter['key']" :wire-model="'filters'" :value="$filterValues[$filter['key']] ?? null"
                        {{ $attributes->merge($safeAttributes) }}></x-dynamic-component>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Bulk Actions Bar --}}
    @if ($showBulkBar())
        <div class="flex items-center gap-2">
            @if ($showBulkActionsDropdown())
                <x-ui.dropdown placement="end" menu>
                    <x-slot:trigger>
                        <x-ui.button variant="outline" size="sm">
                            {{ __('ui.table.bulk_actions') }}
                            <x-ui.icon name="chevron-down" class="h-4 w-4 ml-1"></x-ui.icon>
                        </x-ui.button>
                    </x-slot:trigger>

                    @foreach ($getBulkActions() as $action)
                        <li>
                            <button wire:click="runBulkAction('{{ $action['key'] }}')" type="button"
                                class="w-full text-left">
                                @isset($action['icon'])
                                    <x-ui.icon name="{{ $action['icon'] }}" class="h-4 w-4 mr-2"></x-ui.icon>
                                @endisset
                                {{ $action['label'] }}
                            </button>
                        </li>
                    @endforeach
                </x-ui.dropdown>
            @else
                @foreach ($getBulkActions() as $action)
                    <x-ui.button wire:click="runBulkAction('{{ $action['key'] }}')"
                        variant="{{ $action['variant'] ?? 'outline' }}" :color="$action['color'] ?? null" size="sm">
                        @isset($action['icon'])
                            <x-ui.icon name="{{ $action['icon'] }}" class="h-4 w-4 mr-1"></x-ui.icon>
                        @endisset
                        {{ $action['label'] }}
                    </x-ui.button>
                @endforeach
            @endif
        </div>
    @endif
    {{-- Table --}}
    <x-table
        :rows="$rows"
        :headers="$headers"
        :columns="$columns"
        :actions-per-row="$actionsPerRow"
        :row-click="$rowClick"
        :sort-by="$sortBy"
        :sort-direction="$sortDirection"
        :show-bulk="$showBulk"
        :select-page="$selectPage"
        :select-all="$selectAll"
        :selected="$selected"
        :empty-message="$emptyMessage"
        :empty-icon="$emptyIcon"
        :class="$class"
    ></x-table>

    {{-- Pagination --}}
    @if ($hasPaginator())
        <div class="mt-6">
            <x-table.pagination :paginator="$getPaginator()"></x-table.pagination>
        </div>
    @endif

    {{-- Row Action Modals --}}
    @php
        $rowModalConfig = $getRowActionModalConfig();
    @endphp
    @if ($rowModalConfig)
        <div x-data="{
            actionKey: @js($rowModalConfig['actionKey']),
            {{ $rowModalConfig['modalStateId'] }}: false,
            init() {
                $watch(() => $wire.openRowActionModal, (value) => {
                    if (value === actionKey) {
                        $nextTick(() => { {{ $rowModalConfig['modalStateId'] }} = true; });
                    } else {
                        {{ $rowModalConfig['modalStateId'] }} = false;
                    }
                });
            }
        }">
            <x-ui.confirm-modal
                id="row-action-modal-{{ $rowModalConfig['actionKey'] }}-{{ $rowModalConfig['rowUuid'] }}"
                open-state="{{ $rowModalConfig['modalStateId'] }}">
                <x-slot:actions>
                    <x-ui.button type="button" variant="ghost" wire:click="closeRowActionModal">
                        {{ __('ui.actions.cancel') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="error" wire:click="executeRowActionFromModal">
                        {{ __('ui.actions.confirm') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.confirm-modal>
        </div>
    @endif

    {{-- Bulk Action Modals --}}
    @php
        $bulkModalConfig = $getBulkActionModalConfig();
    @endphp
    @if ($bulkModalConfig)
        <div x-data="{
            actionKey: @js($bulkModalConfig['actionKey']),
            {{ $bulkModalConfig['modalStateId'] }}: false,
            init() {
                $watch(() => $wire.openBulkActionModal, (value) => {
                    if (value === actionKey) {
                        $nextTick(() => { {{ $bulkModalConfig['modalStateId'] }} = true; });
                    } else {
                        {{ $bulkModalConfig['modalStateId'] }} = false;
                    }
                });
            }
        }">
            <x-ui.confirm-modal id="bulk-action-modal-{{ $bulkModalConfig['actionKey'] }}"
                open-state="{{ $bulkModalConfig['modalStateId'] }}">
                <x-slot:actions>
                    <x-ui.button type="button" variant="ghost" wire:click="closeBulkActionModal">
                        {{ __('ui.actions.cancel') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="error" wire:click="executeBulkActionFromModal">
                        {{ __('ui.actions.confirm') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.confirm-modal>
        </div>
    @endif
</div>
