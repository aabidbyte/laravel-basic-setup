/**
 * Confirm Modal Alpine Component
 * Self-registers as 'confirmModal'
 */
export function confirmModal(config = {}) {
    return {
        isOpen: false,
        title: config.title || 'Confirm Action',
        message: config.message || 'Are you sure?',
        confirmLabel: config.confirmLabel || 'Confirm',
        cancelLabel: config.cancelLabel || 'Cancel',
        confirmEvent: null,
        confirmData: null,
        confirmAction: null,

        openModal() {
            this.isOpen = true;
        },

        closeModal() {
            this.isOpen = false;
            this.confirmAction = null;
            this.confirmEvent = null;
            window._confirmModalAction = null;
        },

        executeConfirm() {
            // 1. Dispatch back-event if provided
            if (this.confirmEvent) {
                window.dispatchEvent(
                    new CustomEvent(this.confirmEvent, {
                        detail: this.confirmData,
                        bubbles: true,
                    }),
                );
            }

            // 2. Execute closure if provided (fallback)
            let action = this.confirmAction || window._confirmModalAction;
            if (action && typeof action === 'function') {
                action();
            }

            this.closeModal();
        },

        handleConfirmModal(event) {
            if (!event.detail) return;
            let cfg = event.detail;

            if (cfg.title) this.title = cfg.title;
            if (cfg.message || cfg.content)
                this.message = cfg.message || cfg.content;
            if (cfg.confirmLabel || cfg.confirmText)
                this.confirmLabel = cfg.confirmLabel || cfg.confirmText;
            if (cfg.cancelLabel || cfg.cancelText)
                this.cancelLabel = cfg.cancelLabel || cfg.cancelText;

            this.confirmEvent = cfg.confirmEvent || null;
            this.confirmData = cfg.confirmData || null;
            this.confirmAction = cfg.confirmAction || null;
            if (this.confirmAction)
                window._confirmModalAction = this.confirmAction;

            this.isOpen = true;
        },
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('confirmModal', confirmModal);
});
