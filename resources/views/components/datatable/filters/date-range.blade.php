@props([
    'key' => '',
    'label' => '',
    'value' => null,
    'wireModel' => 'filters',
    'column' => null,
])

@php
    $fromKey = $key . '_from';
    $toKey = $key . '_to';
    $fromValue = is_array($value) ? ($value['from'] ?? null) : null;
    $toValue = is_array($value) ? ($value['to'] ?? null) : null;
@endphp

<div class="form-control">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif
    <div class="flex gap-2">
        <input type="date" wire:model.live="{{ $wireModel }}.{{ $fromKey }}" class="input input-bordered input-sm w-full max-w-xs"
            placeholder="{{ __('ui.table.filters.from') }}" />
        <input type="date" wire:model.live="{{ $wireModel }}.{{ $toKey }}" class="input input-bordered input-sm w-full max-w-xs"
            placeholder="{{ __('ui.table.filters.to') }}" />
    </div>
</div>

