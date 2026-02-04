@props([
    'label' => null,
    'from' => null,
    'to' => null,
    'error' => null,
])

<div class="form-control w-full">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif

    <div class="flex gap-2">
        <x-ui.input type="date"
                    {{ $from }}
                    {{ $attributes->whereStartsWith('wire:model.from') }}
                    class="w-full"
                    placeholder="{{ __('table.date_range.from') }}" />

        <x-ui.input type="date"
                    {{ $to }}
                    {{ $attributes->whereStartsWith('wire:model.to') }}
                    class="w-full"
                    placeholder="{{ __('table.date_range.to') }}" />
    </div>

    @if ($error)
        <label class="label">
            <span class="label-text-alt text-error">{{ $error }}</span>
        </label>
    @endif
</div>
