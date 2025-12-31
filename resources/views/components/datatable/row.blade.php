<tr wire:key="row-{{ $row->uuid }}" wire:click="rowClicked('{{ $row->uuid }}')"
    @class([
        'bg-base-200' => $this->isSelected($row->uuid),
        'cursor-pointer' => $this->rowsAreClickable(),
        'transition-colors hover:bg-base-200/50',
    ])>
    {{-- Selection Checkbox --}}
    <td @click.stop>
        <input type="checkbox" wire:model.live="selected" value="{{ $row->uuid }}"
            wire:key="checkbox-{{ $row->uuid }}" class="checkbox checkbox-sm">
    </td>

    {{-- Data Columns --}}
    @foreach ($this->getColumns() as $column)
        <td style="{{ $column['width'] ? "width: {$column['width']}; max-width: {$column['width']};" : '' }}"
            @class([
                $column['class'],
                'whitespace-nowrap' => $column['nowrap'],
                'truncate' => (bool) $column['width'],
            ])>
            {!! $this->renderColumn($column, $row) !!}
        </td>
    @endforeach

    {{-- Actions Dropdown --}}
    <td @click.stop>
        {!! $this->renderRowActions($row) !!}
    </td>
</tr>
