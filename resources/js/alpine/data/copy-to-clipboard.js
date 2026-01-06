/**
 * Copy to Clipboard Alpine Component
 * Self-registers as 'copyToClipboard'
 */
export function copyToClipboard(text = '') {
    return {
        copied: false,
        text: text,

        async copy() {
            try {
                await navigator.clipboard.writeText(this.text);
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 2000);
            } catch (err) {
                console.error('Copy failed:', err);
            }
        },
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('copyToClipboard', copyToClipboard);
});
