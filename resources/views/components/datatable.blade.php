{{--
    DataTable Component
    All props are handled by the Datatable component class (App\View\Components\Datatable)
    The $viewData property is automatically available from the component class
--}}

<div class="space-y-4 {{ $viewData->getClass() }}">
    {{-- Search Bar (by default) --}}
    @if ($viewData->isShowSearch())
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-2">
                <x-ui.input type="text" wire:model.live.debounce.300ms="search" :placeholder="$viewData->getSearchPlaceholder()"
                    class="max-w-xs"></x-ui.input>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    @if ($viewData->hasFilters())
        <div class="flex flex-wrap gap-4">
            @foreach ($viewData->getFilters() as $filter)
                @php
                    $processedFilter = $viewData->processFilter($filter);
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
    @if ($viewData->showBulkBar())
        <div class="flex items-center gap-2">
            @if ($viewData->showBulkActionsDropdown())
                <x-ui.dropdown placement="end" menu>
                    <x-slot:trigger>
                        <x-ui.button variant="outline" size="sm">
                            {{ __('ui.table.bulk_actions') }}
                            <x-ui.icon name="chevron-down" class="h-4 w-4 ml-1"></x-ui.icon>
                        </x-ui.button>
                    </x-slot:trigger>

                    @foreach ($viewData->getBulkActions() as $action)
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
                @foreach ($viewData->getBulkActions() as $action)
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
    <x-table :view-data="$viewData"></x-table>

    {{-- Pagination --}}
    @if ($viewData->hasPaginator())
        <div class="mt-6">
            <x-table.pagination :paginator="$viewData->getPaginator()"></x-table.pagination>
        </div>
    @endif

    {{-- Row Action Modals --}}
    @php
        $rowModalConfig = $viewData->getRowActionModalConfig();
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
        $bulkModalConfig = $viewData->getBulkActionModalConfig();
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
