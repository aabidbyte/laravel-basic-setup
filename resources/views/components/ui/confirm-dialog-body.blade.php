@props([
    'title' => null,
    'content' => null,
    'confirmLabel' => __('actions.confirm'),
    'cancelLabel' => __('actions.cancel'),
    'onConfirm',
    'onCancel',
    // Alpine.js binding keys (string names of JS variables)
    'alpineTitle' => null,
    'alpineMessage' => null,
    'alpineConfirmLabel' => null,
    'alpineCancelLabel' => null,
])

<div class="flex items-start gap-4 p-6">
    <div class="flex-shrink-0">
        <x-ui.icon
            name="exclamation-triangle"
            class="h-8 w-8 text-error"
        ></x-ui.icon>
    </div>
    <div class="flex-1">
        <h3
            class="text-lg font-bold"
            @if ($alpineTitle) x-text="{{ $alpineTitle }}" @endif
        >
            {{ $alpineTitle ? '' : $title }}
        </h3>

        <div
            class="py-4 text-base-content/70"
            @if ($alpineMessage) x-text="{{ $alpineMessage }}" @endif
        >
            {{ $alpineMessage ? '' : $content }}
        </div>

        {{ $slot }}
    </div>
</div>

<div class="flex justify-end gap-3 px-6 pb-6 bg-base-100">
    <x-ui.button
        variant="ghost"
        @click="{{ $onCancel }}"
    >
        <span @if ($alpineCancelLabel) x-text="{{ $alpineCancelLabel }}" @endif>
            {{ $alpineCancelLabel ? '' : $cancelLabel }}
        </span>
    </x-ui.button>

    <x-ui.button
        variant="error"
        @click="{{ $onConfirm }}"
    >
        <span @if ($alpineConfirmLabel) x-text="{{ $alpineConfirmLabel }}" @endif>
            {{ $alpineConfirmLabel ? '' : $confirmLabel }}
        </span>
    </x-ui.button>
</div>
