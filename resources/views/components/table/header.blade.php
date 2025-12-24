@props([
    'columns' => [],
    'sortBy' => null,
    'sortDirection' => 'asc',
    'showBulk' => false,
    'selectPage' => false,
    'selectAll' => false,
    'showActions' => false,
])

@php
    use App\Constants\DataTable\DataTableUi;

    // Process header columns
    $processHeaderColumn = function (array $column): array {
        // Handle both DSL structure (HeaderItem) and legacy structure
        // DSL: 'sortKey', 'column' => ['key' => ...], 'showInViewPortsOnly'
        // Legacy: 'key', 'hidden', 'responsive'
        $hidden = $column[DataTableUi::HEADER_HIDDEN] ?? false;
        $responsive = $column[DataTableUi::HEADER_RESPONSIVE] ?? null;

        // Check for DSL structure: showInViewPortsOnly means hidden by default
        if (
            isset($column[DataTableUi::HEADER_SHOW_IN_VIEWPORTS_ONLY]) &&
            is_array($column[DataTableUi::HEADER_SHOW_IN_VIEWPORTS_ONLY])
        ) {
            $viewports = $column[DataTableUi::HEADER_SHOW_IN_VIEWPORTS_ONLY];
            if (!empty($viewports)) {
                $hidden = true; // Hidden by default when showInViewPortsOnly is set
                $responsive = implode(' ', array_map(fn($vp) => $vp . ':table-cell', $viewports));
            }
        }

        $thClass = $responsive ? 'hidden ' . $responsive : '';

        // Get column key: from 'key' (legacy), 'sortKey' (DSL), or column['key'] (DSL)
        $columnKey =
            $column[DataTableUi::HEADER_KEY] ??
            ($column[DataTableUi::HEADER_SORT_KEY] ??
                ($column[DataTableUi::HEADER_COLUMN][DataTableUi::HEADER_KEY] ?? null));

        $sortable = ($column[DataTableUi::HEADER_SORTABLE] ?? false) && $columnKey;

        return [
            DataTableUi::PROCESSED_HEADER_HIDDEN => $hidden,
            DataTableUi::PROCESSED_HEADER_RESPONSIVE => $responsive,
            DataTableUi::PROCESSED_HEADER_TH_CLASS => $thClass,
            DataTableUi::PROCESSED_HEADER_COLUMN_KEY => $columnKey,
            DataTableUi::PROCESSED_HEADER_SORTABLE => $sortable,
        ];
    };
@endphp

<thead>
    <tr>
        @if ($showBulk)
            <th>
                <input type="checkbox" wire:model="selectPage" wire:click="toggleSelectPage" class="checkbox checkbox-sm" />
            </th>
        @endif

        @foreach ($columns as $column)
            @php
                $columnData = $processHeaderColumn($column);
            @endphp
            @if (!$columnData[DataTableUi::PROCESSED_HEADER_HIDDEN])
                <th @class([
                    $columnData[DataTableUi::PROCESSED_HEADER_TH_CLASS] =>
                        $columnData[DataTableUi::PROCESSED_HEADER_RESPONSIVE],
                ])>
                    <div class="flex items-center gap-2">
                        @if ($columnData[DataTableUi::PROCESSED_HEADER_SORTABLE] && $columnData[DataTableUi::PROCESSED_HEADER_COLUMN_KEY])
                            <button wire:click="sortBy('{{ $columnData[DataTableUi::PROCESSED_HEADER_COLUMN_KEY] }}')"
                                class="flex items-center gap-2 hover:text-primary">
                                <span>{{ $column[DataTableUi::HEADER_LABEL] ?? '' }}</span>
                                @if ($sortBy === $columnData[DataTableUi::PROCESSED_HEADER_COLUMN_KEY])
                                    <x-ui.icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="h-4 w-4"></x-ui.icon>
                                @else
                                    <x-ui.icon name="arrows-up-down" class="h-4 w-4 opacity-30"></x-ui.icon>
                                @endif
                            </button>
                        @else
                            <span>{{ $column[DataTableUi::HEADER_LABEL] ?? '' }}</span>
                        @endif

                        {{-- Additional header content (buttons, etc.) --}}
                        @if (isset($column[DataTableUi::HEADER_ACTIONS]) && is_array($column[DataTableUi::HEADER_ACTIONS]))
                            @foreach ($column[DataTableUi::HEADER_ACTIONS] as $action)
                                @php
                                    $actionAttributes = '';
                                    $hasAttributes = false;
                                    if (
                                        isset($action[DataTableUi::HEADER_ACTION_ATTRIBUTES]) &&
                                        is_array($action[DataTableUi::HEADER_ACTION_ATTRIBUTES]) &&
                                        !empty($action[DataTableUi::HEADER_ACTION_ATTRIBUTES])
                                    ) {
                                        $hasAttributes = true;
                                        $actionAttributes = collect($action[DataTableUi::HEADER_ACTION_ATTRIBUTES])
                                            ->map(
                                                fn($value, $key) => $key .
                                                    '="' .
                                                    htmlspecialchars($value, ENT_QUOTES, 'UTF-8') .
                                                    '"',
                                            )
                                            ->implode(' ');
                                    }
                                @endphp
                                @if (isset($action[DataTableUi::HEADER_ACTION_COMPONENT]))
                                    {{-- Note: Custom attributes are not supported for dynamic components due to Blade parsing limitations --}}
                                    <x-dynamic-component :component="$action[DataTableUi::HEADER_ACTION_COMPONENT]"
                                        wire:click.stop="{{ $action[DataTableUi::HEADER_ACTION_WIRE_CLICK] ?? '' }}">
                                        @if (isset($action[DataTableUi::HEADER_ACTION_SLOT]))
                                            {!! $action[DataTableUi::HEADER_ACTION_SLOT] !!}
                                        @endif
                                    </x-dynamic-component>
                                @elseif (isset($action[DataTableUi::HEADER_ACTION_BUTTON]) || isset($action[DataTableUi::HEADER_ACTION_WIRE_CLICK]))
                                    @if ($hasAttributes)
                                        <button type="button"
                                            wire:click.stop="{{ $action[DataTableUi::HEADER_ACTION_WIRE_CLICK] ?? '' }}"
                                            class="{{ $action[DataTableUi::HEADER_ACTION_CLASS] ?? 'btn btn-sm btn-ghost' }}"
                                            {!! $actionAttributes !!}>
                                            @if (isset($action[DataTableUi::HEADER_ACTION_ICON]))
                                                <x-ui.icon name="{{ $action[DataTableUi::HEADER_ACTION_ICON] }}"
                                                    class="h-4 w-4"></x-ui.icon>
                                            @endif
                                            @if (isset($action[DataTableUi::HEADER_ACTION_LABEL]))
                                                <span>{{ $action[DataTableUi::HEADER_ACTION_LABEL] }}</span>
                                            @endif
                                        </button>
                                    @else
                                        <button type="button"
                                            wire:click.stop="{{ $action[DataTableUi::HEADER_ACTION_WIRE_CLICK] ?? '' }}"
                                            class="{{ $action[DataTableUi::HEADER_ACTION_CLASS] ?? 'btn btn-sm btn-ghost' }}">
                                            @if (isset($action[DataTableUi::HEADER_ACTION_ICON]))
                                                <x-ui.icon name="{{ $action[DataTableUi::HEADER_ACTION_ICON] }}"
                                                    class="h-4 w-4"></x-ui.icon>
                                            @endif
                                            @if (isset($action[DataTableUi::HEADER_ACTION_LABEL]))
                                                <span>{{ $action[DataTableUi::HEADER_ACTION_LABEL] }}</span>
                                            @endif
                                        </button>
                                    @endif
                                @endif
                            @endforeach
                        @endif

                        {{-- Support for custom header content slot --}}
                        @if (isset($column[DataTableUi::HEADER_SLOT]))
                            <div wire:click.stop>
                                {!! $column[DataTableUi::HEADER_SLOT] !!}
                            </div>
                        @endif
                    </div>
                </th>
            @endif
        @endforeach

        @if ($showActions)
            <th></th>
        @endif
    </tr>
</thead>
