{{--
    Copy Button Component
    
    A reusable copy-to-clipboard button that works in both HTTPS and HTTP environments.
    Uses the copyToClipboard Alpine component with fallback mechanism.
    
    Props:
    - text: The text to copy (required)
    - size: Button size (xs, sm, md, lg) - default: sm
    - variant: Button variant (ghost, primary, secondary, etc.) - default: ghost
    - showText: Whether to show button text (default: false, icon only)
    - copyText: Text to show on button (default: 'Copy')
    - copiedText: Text to show after copy (default: 'Copied!')
    - class: Additional classes
--}}
@props([
    'text' => '',
    'size' => 'sm',
    'variant' => 'ghost',
    'showText' => false,
    'copyText' => null,
    'copiedText' => null,
])

@php
    $copyText = $copyText ?? __('actions.copy');
    $copiedText = $copiedText ?? __('actions.copied');
@endphp

<div
    x-data="copyToClipboard({ text: @js($text) })"
    {{ $attributes->only('class') }}
>
    <x-ui.button
        @click="copy()"
        size="{{ $size }}"
        variant="{{ $variant }}"
        type="button"
    >
        <x-ui.icon
            x-show="!copied && !error"
            name="clipboard"
            size="sm"
        ></x-ui.icon>
        <x-ui.icon
            x-show="copied"
            x-cloak
            name="check"
            size="sm"
            class="text-success"
        ></x-ui.icon>
        <x-ui.icon
            x-show="error"
            x-cloak
            name="x-mark"
            size="sm"
            class="text-error"
        ></x-ui.icon>
        @if ($showText)
            <span x-text="copied ? @js($copiedText) : @js($copyText)"></span>
        @endif
    </x-ui.button>
</div>
