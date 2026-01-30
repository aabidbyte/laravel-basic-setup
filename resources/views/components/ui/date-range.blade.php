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
        <input type="date"
               {{ $from }}
               {{ $attributes->whereStartsWith('wire:model.from') }}
               class="input input-bordered w-full"
               placeholder="{{ __('From') }}" />

        <input type="date"
               {{ $to }}
               {{ $attributes->whereStartsWith('wire:model.to') }}
               class="input input-bordered w-full"
               placeholder="{{ __('To') }}" />
    </div>

    @if ($error)
        <label class="label">
            <span class="label-text-alt text-error">{{ $error }}</span>
        </label>
    @endif
</div>
