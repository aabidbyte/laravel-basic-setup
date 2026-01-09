/**
 * Copy to Clipboard Alpine Component
 * Self-registers as 'copyToClipboard'
 *
 * Features:
 * - Clipboard API with fallback for non-HTTPS environments
 * - Configurable success/error messages
 * - Automatic state reset after copy
 */
export function copyToClipboard(config = {}) {
    // Support both simple string and config object
    const text = typeof config === 'string' ? config : config.text || '';
    const copiedText =
        typeof config === 'object' ? config.copiedText || 'Copied!' : 'Copied!';
    const errorText =
        typeof config === 'object'
            ? config.errorText || 'Copy failed'
            : 'Copy failed';

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
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('copyToClipboard', copyToClipboard);
});
