<tr
    wire:key="row-{{ $row->uuid }}"
    @if ($this->rowsAreClickable()) @click="@if ($this->rowClickOpensModal())window.dispatchEvent(new CustomEvent('datatable-modal-loading')); @endif$wire.handleRowClick('{{ $row->uuid }}')"
    @endif
    @class([
        'bg-base-200' => $this->isSelected($row->uuid),
        'cursor-pointer' => $this->rowsAreClickable(),
        'transition-colors hover:bg-base-200/50',
    ])>
    {{-- Selection Checkbox --}}
    <td @click.stop>
        <x-ui.checkbox
            wire:model.live="selected"
            value="{{ $row->uuid }}"
            wire:key="checkbox-{{ $row->uuid }}"
            size="sm"
        />
    </td>

    {{-- Data Columns --}}
    @foreach ($this->getColumns() as $column)
        <td
            style="{{ $column['width'] ? "width: {$column['width']}; max-width: {$column['width']};" : '' }}"
            @class([
                $column['class'],
                'whitespace-nowrap' => $column['nowrap'],
                'truncate' => (bool) $column['width'],
            ])
        >
            {!! $this->renderColumn($column, $row) !!}
        </td>
    @endforeach

    {{-- Actions Dropdown --}}
    <td @click.stop>
        {!! $this->renderRowActions($row) !!}
    </td>
</tr>
