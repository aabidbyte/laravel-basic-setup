<tr
    wire:key="row-{{ $row->uuid }}"
    @if ($datatable->rowsAreClickable()) @click="@if ($datatable->rowClickOpensModal())window.dispatchEvent(new CustomEvent('datatable-modal-loading')); @endif$wire.handleRowClick('{{ $row->uuid }}')"
    @endif
    @class([
        'bg-base-200' => $datatable->isSelected($row->uuid),
        'cursor-pointer' => $datatable->rowsAreClickable(),
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
    @foreach ($datatable->getColumns() as $column)
        <td
            style="{{ $column['width'] ? "width: {$column['width']}; max-width: {$column['width']};" : '' }}"
            @class([
                $column['class'],
                'whitespace-nowrap' => $column['nowrap'],
                'truncate' => (bool) $column['width'],
            ])
        >
            {!! $datatable->renderColumn($column, $row) !!}
        </td>
    @endforeach

    {{-- Actions Dropdown --}}
    <td @click.stop class="sticky right-0 z-10 sticky-action-cell text-end">
        {!! $datatable->renderRowActions($row) !!}
    </td>
</tr>
