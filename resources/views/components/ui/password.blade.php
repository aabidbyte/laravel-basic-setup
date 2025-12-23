@props([
    'label' => null,
    'error' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id') ?? uniqid('password-');
    $hasError = $error || ($errors->has($attributes->get('name')) ?? false);
@endphp

<label class="flex flex-col gap-2">
    @if ($label)
        <div class="label">
            <span class="label-text">{{ $label }}@if ($required)
                    <span class="text-error">*</span>
                @endif
            </span>
            @isset($labelAppend)
                {{ $labelAppend }}
            @endisset
        </div>
    @endif
    <div class="relative overflow-visible" x-data="{ showPassword: false }">
        <input type="password" x-bind:type="showPassword ? 'text' : 'password'"
            {{ $attributes->merge(['class' => 'input input-bordered w-full pr-10' . ($hasError ? ' input-error' : '')])->except(['label', 'error', 'type']) }}
            id="{{ $inputId }}" />
        <button type="button" @click.stop="showPassword = !showPassword"
            class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-ghost btn-sm btn-circle p-0 h-8 w-8 min-h-0 z-10"
            :aria-label="showPassword ? 'Hide password' : 'Show password'" tabindex="0">
            <span x-show="!showPassword" x-cloak>
                <x-ui.icon name="eye" class="h-5 w-5"></x-ui.icon>
            </span>
            <span x-show="showPassword" x-cloak>
                <x-ui.icon name="eye-slash" class="h-5 w-5"></x-ui.icon>
            </span>
        </button>
    </div>
    @if ($error || ($errors->has($attributes->get('name')) ?? false))
        <div class="label">
            <span class="label-text-alt text-error">{{ $error ?? $errors->first($attributes->get('name')) }}</span>
        </div>
    @endif
</label>
