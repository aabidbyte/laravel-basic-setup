<tr wire:key="row-{{ $row->uuid }}"
    @if ($datatable->rowsAreClickable()) wire:click="handleRowClick('{{ $row->uuid }}')"
        @if ($datatable->rowClickOpensModal()) @click="$dispatch('datatable-modal-loading')" @endif
    @endif
    @class([
        '!bg-secondary' => $datatable->isSelected($row->uuid),
        'cursor-pointer' => $datatable->rowsAreClickable(),
        'transition-colors hover:bg-accent',
    ])>
    {{-- Selection Checkbox - only render if bulk actions are defined --}}
    @if ($datatable->hasBulkActions())
        <td @click.stop
            class="sticky-action-cell sticky left-0 z-10 p-1">
            <x-ui.checkbox wire:model.live="selected"
                           value="{{ $row->uuid }}"
                           wire:key="checkbox-{{ $row->uuid }}"
                           size="xs" />
        </td>
    @endif

    {{-- Data Columns --}}
    @foreach ($datatable->getColumns() as $column)
        <td style="{{ $column['width'] ? "width: {$column['width']}; max-width: {$column['width']};" : '' }}"
            @class([
                $column['class'],
                'whitespace-nowrap' => $column['nowrap'],
                'truncate' => (bool) $column['width'],
            ])>
            {!! $datatable->renderColumn($column, $row) !!}
        </td>
    @endforeach

    {{-- Actions Dropdown - only render if row actions are defined --}}
    @if ($datatable->hasRowActions())
        <td @click.stop
            class="sticky-action-cell sticky right-0 z-10 text-end">
            {!! $datatable->renderRowActions($row) !!}
        </td>
    @endif
</tr>
