@props([
    'key' => '',
    'label' => '',
    'placeholder' => '',
    'value' => null,
    'wireModel' => 'filters',
])

<div class="form-control">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif
    <select wire:model.live="{{ $wireModel }}.{{ $key }}" class="select select-bordered select-sm w-full max-w-xs">
        <option value="">{{ $placeholder }}</option>
        <option value="1">{{ __('ui.table.yes') }}</option>
        <option value="0">{{ __('ui.table.no') }}</option>
    </select>
</div>

