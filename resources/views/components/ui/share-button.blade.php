@props([
    'url' => null, // URL to share (if null, uses current full URL)
    'tooltipText' => null, // Custom tooltip text (defaults to translation)
    'size' => 'md', // Button size: xs, sm, md, lg
    'style' => 'ghost', // Button style
])

@php
    // Generate URL if not provided
    $shareUrl = $url ?? request()->fullUrl();
    $tooltipText = $tooltipText ?? __('table.share_page');
    // We don't use the copied text in the tooltip anymore, we specific toast or just tooltip change
$copiedText = __('table.url_copied');
@endphp

<div x-data="shareButton({
    url: @js($shareUrl),
    initialTooltip: @js($tooltipText),
    copiedText: @js($copiedText),
    copyFailedText: @js(__('table.copy_failed') ?? 'Copy failed')
})"
     class="relative inline-block">
    <x-ui.tooltip :text="$tooltipText"
                  placement="top">
        <x-ui.button @click="copyUrl()"
                     type="button"
                     variant="{{ $style }}"
                     size="{{ $size }}"
                     class="btn-square data-loading:opacity-50 data-loading:pointer-events-none"
                     aria-label="{{ $tooltipText }}">
            <x-ui.icon name="share"
                       class="h-5 w-5" />
        </x-ui.button>
    </x-ui.tooltip>

    @assets
        <script>
            (function() {
                const register = () => {
                    Alpine.data('shareButton', (config) => ({
                        url: config.url,
                        tooltipText: config.initialTooltip,

                        async copyUrl() {
                            try {
                                await navigator.clipboard.writeText(this.url);

                                const originalText = config.initialTooltip;
                                this.tooltipText = config.copiedText;

                                setTimeout(() => {
                                    this.tooltipText = originalText;
                                }, 2000);
                            } catch (err) {
                                console.error('Failed to copy text: ', err);

                                const originalText = config.initialTooltip;
                                this.tooltipText = config.copyFailedText;

                                setTimeout(() => {
                                    this.tooltipText = originalText;
                                }, 2000);
                            }
                        }
                    }));
                };

                if (window.Alpine) {
                    register();
                } else {
                    document.addEventListener('alpine:init', register);
                }
            })();
        </script>
    @endassets
