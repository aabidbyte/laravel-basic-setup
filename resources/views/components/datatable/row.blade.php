<tr wire:key="row-{{ $row->uuid }}"
    @if ($this->rowsAreClickable()) x-data="tableRow('{{ $row->uuid }}')"
        @click="handleClick($event)"
        @if ($this->rowClickOpensModal()) @click="$dispatch('this-modal-loading')" @endif
    @endif
    @class([
        '!bg-secondary' => $this->isSelected($row->uuid),
        'cursor-pointer' => $this->rowsAreClickable(),
        'transition-colors hover:bg-accent',
    ])>
    {{-- Selection Checkbox - only render if bulk actions are defined --}}
    @if ($this->hasBulkActions())
        <td wire:key="row-{{ $row->uuid }}-checkbox"
            class="sticky-action-cell sticky left-0 z-10 p-1">
            <x-ui.checkbox wire:model.live="selected"
                           value="{{ $row->uuid }}"
                           wire:key="checkbox-{{ $row->uuid }}"
                           size="xs" />
        </td>
    @endif

    {{-- Data Columns --}}
    @foreach ($this->getColumns() as $column)
        <td wire:key="row-{{ $row->uuid }}-{{ $column['field'] ?? $loop->index }}"
            style="{{ $column['width'] ? "width: {$column['width']}; max-width: {$column['width']};" : '' }}"
            @class([
                $column['class'],
                'whitespace-nowrap' => $column['nowrap'],
                'truncate' => (bool) $column['width'],
            ])>
            @if ($column['searchable'] && $this->search)
                {{-- Client-side highlighting for all searchable columns --}}
                @php
                    $cellValue = $this->renderColumn($column, $row);
                @endphp
                <span x-data="highlightedCell('{{ addslashes($cellValue) }}', '{{ addslashes($this->search) }}')"></span>
            @else
                {{-- No highlighting for non-searchable columns --}}
                {!! $this->renderColumn($column, $row) !!}
            @endif
        </td>
    @endforeach

    {{-- Actions Dropdown - only render if row actions are defined --}}
    @if ($this->hasRowActions())
        <td wire:key="row-{{ $row->uuid }}-actions"
            class="sticky-action-cell sticky right-0 z-10 text-end">
            {!! $this->renderRowActions($row) !!}
        </td>
    @endif
</tr>
