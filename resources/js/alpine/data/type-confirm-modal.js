/**
 * Type Confirm Modal - CSP-Safe Alpine Data Component
 *
 * A modal that requires the user to type an item's name before confirming
 * a dangerous action like permanent deletion.
 *
 * Self-registers as 'typeConfirmModal'
 */
export function typeConfirmModal(config = {}) {
    return {
        isOpen: false,
        itemLabel: config.itemLabel || '',
        confirmText: '',
        onConfirm: config.onConfirm || (() => {}),

        /**
         * Open the confirmation modal
         */
        openModal() {
            this.confirmText = '';
            this.isOpen = true;
        },

        /**
         * Close the modal
         */
        closeModal() {
            this.isOpen = false;
            this.confirmText = '';
        },

        /**
         * Check if the typed text matches the item label
         */
        get isConfirmValid() {
            return this.confirmText.trim() === this.itemLabel.trim();
        },

        /**
         * Handle the confirm action
         */
        confirm() {
            if (this.isConfirmValid) {
                this.onConfirm();
                this.closeModal();
            }
        },
    };
}

// Self-register when Alpine initializes
document.addEventListener('alpine:init', () => {
    window.Alpine.data('typeConfirmModal', typeConfirmModal);
});
