@props([
    'url' => null, // URL to share (if null, uses current full URL)
    'tooltipText' => null, // Custom tooltip text (defaults to translation)
    'size' => 'md', // Button size: xs, sm, md, lg
    'style' => 'ghost', // Button style
])

@php
    // Generate URL if not provided
    $shareUrl = $url ?? request()->fullUrl();
    $tooltipText = $tooltipText ?? __('ui.table.share_page');
    $copiedText = __('ui.table.url_copied');
@endphp

<div x-data="{
    copied: false,
    tooltipText: null,
    copyUrl(url) {
        console.log('[Share Button] Copying URL:', url);

        // Check if Clipboard API is available (requires secure context)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                console.log('[Share Button] URL copied successfully via Clipboard API');
                this.copied = true;
                this.tooltipText = '{{ $copiedText }}';
                setTimeout(() => {
                    this.copied = false;
                    this.tooltipText = '{{ $tooltipText }}';
                }, 2000);
            }).catch((error) => {
                console.error('[Share Button] Clipboard API failed:', error);
                // Fallback if clipboard API fails
                this.fallbackCopy(url);
            });
        } else {
            console.log('[Share Button] Clipboard API not available, using fallback');
            // Fallback for browsers without Clipboard API support
            this.fallbackCopy(url);
        }
    },
    fallbackCopy(url) {
        console.log('[Share Button] Using fallback copy method for URL:', url);
        try {
            const textarea = document.createElement('textarea');
            textarea.value = url;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            const success = document.execCommand('copy');
            document.body.removeChild(textarea);

            if (success) {
                console.log('[Share Button] URL copied successfully via fallback method');
            } else {
                console.warn('[Share Button] Fallback copy command returned false');
            }

            this.copied = true;
            this.tooltipText = '{{ $copiedText }}';
            setTimeout(() => {
                this.copied = false;
                this.tooltipText = '{{ $tooltipText }}';
            }, 2000);
        } catch (err) {
            console.error('[Share Button] Fallback copy failed:', err);
            // If copy fails, show error in tooltip
            this.tooltipText = '{{ __('ui.table.copy_failed') ?? 'Copy failed' }}';
            setTimeout(() => {
                this.tooltipText = '{{ $tooltipText }}';
            }, 2000);
        }
    }
}" x-init="tooltipText = '{{ $tooltipText }}'" class="relative">
    <div class="tooltip tooltip-top" x-bind:data-tip="tooltipText">
        <button @click="copyUrl(@js($shareUrl))" type="button"
            class="btn btn-{{ $style }} btn-{{ $size }} btn-square" aria-label="{{ $tooltipText }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z">
                </path>
            </svg>
        </button>
    </div>
</div>
