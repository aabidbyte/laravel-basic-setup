/**
 * Confirm Modal Helper
 *
 * Provides a global confirmModal() function that dispatches a custom event
 * to the confirm-modal component.
 */

window.confirmModal = function(options) {
    window.dispatchEvent(new CustomEvent('confirm-modal', {
        detail: options
    }));
};
