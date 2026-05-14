<tr wire:key="row-{{ $row->uuid }}"
    @php
        $isClickable = $this->rowsAreClickable();
        $clickOpensModal = $this->rowClickOpensModal();
    @endphp
    @class([
        'cursor-pointer' => $isClickable,
        'transition-colors hover:bg-accent',
    ])
    @if ($isClickable)
        data-row-uuid="{{ $row->uuid }}"
        x-data="tableRow()"
        @click="handleClick($event) || ({{ $clickOpensModal ? 'true' : 'false' }} && $dispatch('this-modal-loading'))"
    @endif
    wire:bind:class="selected.includes('{{ $row->uuid }}') ? '!bg-secondary' : ''">
    {{-- Selection Checkbox - only render if bulk actions are defined --}}
    @if ($this->hasBulkActions())
        <td wire:key="row-{{ $row->uuid }}-checkbox"
            class="sticky-action-cell sticky left-0 z-10 p-1">
            <x-ui.checkbox wire:model="selected"
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
            @php
                $cellValue = (string) $this->renderColumn($column, $row);
            @endphp
            @if ($column['searchable'] && $this->search)
                {{-- Client-side highlighting for all searchable columns --}}
                <span x-data="datatableHighlightedCell('{{ addslashes($cellValue) }}', '{{ addslashes($this->search) }}')"></span>
            @else
                {{-- No highlighting for non-searchable columns --}}
                {!! $cellValue !!}
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
