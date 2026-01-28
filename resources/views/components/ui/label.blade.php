@props([
    'for' => null,
    'required' => false,
    'text' => null,
    'variant' => 'header', // header, inline, plain
])

@php
    $classMap = [
        'header' => 'label pt-0 pb-1',
        'inline' => 'label cursor-pointer justify-start gap-3',
        'plain' => 'label',
    ];
    $classes = $classMap[$variant] ?? $classMap['header'];
@endphp

<label {{ $attributes->merge(['class' => $classes]) }}
       @if ($for) for="{{ $for }}" @endif>
    @if ($variant === 'header')
        <div class="flex w-full items-center justify-between">
            <span class="label-text flex items-center gap-1 font-semibold">
                {{ $text ?? $slot }}
                @if ($required)
                    <span class="text-error text-xs"
                          title="{{ __('actions.required') }}">*</span>
                @endif
            </span>
            @isset($labelAppend)
                <div class="label-text-alt text-base-content/50 text-[10px] font-bold uppercase tracking-wider">
                    {{ $labelAppend }}
                </div>
            @endisset
        </div>
    @else
        {{ $text ?? $slot }}
    @endif
</label>
