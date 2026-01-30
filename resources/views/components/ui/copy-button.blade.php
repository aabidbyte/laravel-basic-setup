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
    'color' => null,
    'showText' => false,
    'copyText' => null,
    'copiedText' => null,
])

@php
    $copyText = $copyText ?? __('actions.copy');
    $copiedText = $copiedText ?? __('actions.copied');
@endphp

<div x-data="copyToClipboard({ text: @js($text) })"
     {{ $attributes->only('class') }}>
    <x-ui.button @click="copy()"
                 size="{{ $size }}"
                 variant="{{ $variant }}"
                 color="{{ $color }}"
                 type="button">
        <x-ui.icon x-show="!copied && !error"
                   name="clipboard"
                   size="sm"></x-ui.icon>
        <x-ui.icon x-show="copied"
                   x-cloak
                   name="check"
                   size="sm"
                   class="text-success"></x-ui.icon>
        <x-ui.icon x-show="error"
                   x-cloak
                   name="x-mark"
                   size="sm"
                   class="text-error"></x-ui.icon>
        @if ($showText)
            <span x-text="copied ? @js($copiedText) : @js($copyText)"></span>
        @endif
    </x-ui.button>
</div>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('copyToClipboard', (config = {}) => {
                    const text = typeof config === 'string' ? config : config.text || '';
                    const copiedText =
                        typeof config === 'object' ? config.copiedText || 'Copied!' : 'Copied!';
                    const errorText =
                        typeof config === 'object' ?
                        config.errorText || 'Copy failed' :
                        'Copy failed';

                    return {
                        copied: false,
                        error: false,
                        text: text,

                        async copy(overrideText = null) {
                            const textToCopy = overrideText || this.text;

                            // Check if Clipboard API is available (requires secure context)
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                try {
                                    await navigator.clipboard.writeText(textToCopy);
                                    this.onCopySuccess();
                                } catch (err) {
                                    console.error(
                                        '[copyToClipboard] Clipboard API failed:',
                                        err,
                                    );
                                    this.fallbackCopy(textToCopy);
                                }
                            } else {
                                this.fallbackCopy(textToCopy);
                            }
                        },

                        fallbackCopy(text) {
                            try {
                                const textarea = document.createElement('textarea');
                                textarea.value = text;
                                textarea.style.position = 'fixed';
                                textarea.style.opacity = '0';
                                textarea.style.pointerEvents = 'none';
                                document.body.appendChild(textarea);
                                textarea.select();
                                const success = document.execCommand('copy');
                                document.body.removeChild(textarea);

                                if (success) {
                                    this.onCopySuccess();
                                } else {
                                    this.onCopyError();
                                }
                            } catch (err) {
                                console.error('[copyToClipboard] Fallback copy failed:', err);
                                this.onCopyError();
                            }
                        },

                        onCopySuccess() {
                            this.copied = true;
                            this.error = false;
                            setTimeout(() => {
                                this.copied = false;
                            }, 2000);
                        },

                        onCopyError() {
                            this.error = true;
                            this.copied = false;
                            setTimeout(() => {
                                this.error = false;
                            }, 2000);
                        },
                    };
                });
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
