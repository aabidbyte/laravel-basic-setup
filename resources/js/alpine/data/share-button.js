/**
 * Share Button Alpine Component
 * Self-registers as 'shareButton'
 */
export function shareButton(config = {}) {
    return {
        copied: false,
        tooltipText: config.initialTooltip || '',
        shareUrl: config.url || '',
        copiedText: config.copiedText || 'Copied!',
        initialTooltip: config.initialTooltip || '',
        copyFailedText: config.copyFailedText || 'Copy failed',

        async copyUrl() {
            const url = this.shareUrl;
            
            // Check if Clipboard API is available (requires secure context)
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(url);
                    this.onCopySuccess();
                } catch (error) {
                    console.error('[Share Button] Clipboard API failed:', error);
                    this.fallbackCopy(url);
                }
            } else {
                this.fallbackCopy(url);
            }
        },

        fallbackCopy(url) {
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
                    this.onCopySuccess();
                } else {
                    this.onCopyError();
                }
            } catch (err) {
                console.error('[Share Button] Fallback copy failed:', err);
                this.onCopyError();
            }
        },

        onCopySuccess() {
            this.copied = true;
            this.tooltipText = this.copiedText;
            setTimeout(() => {
                this.copied = false;
                this.tooltipText = this.initialTooltip;
            }, 2000);
        },

        onCopyError() {
            this.tooltipText = this.copyFailedText;
            setTimeout(() => {
                this.tooltipText = this.initialTooltip;
            }, 2000);
        }
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('shareButton', shareButton);
});
