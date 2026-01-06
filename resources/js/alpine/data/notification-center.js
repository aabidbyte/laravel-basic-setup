/**
 * Alpine.js Data Components: Notification Center
 *
 * Handles real-time notification updates for notification center and dropdown components.
 */

/**
 * Notification Center Alpine.js Data Component
 * Manages notification center page updates
 */
export function notificationCenter() {
    return {
        unsubscribe: null,
        pendingDeleteId: null,
        _confirmListener: null,

        init() {
            if (!window.Alpine) {
                console.error(
                    '[Notification Center] Alpine.js is not available',
                );
                return;
            }

            const store = window.Alpine.store('notifications');
            if (!store) {
                console.error(
                    '[Notification Center] Notifications store is not available',
                );
                return;
            }

            this.unsubscribe = store.subscribe((eventName) => {
                if (eventName !== 'notification.changed') {
                    return;
                }

                // The refresh logic is handled by the template's event listener:
                // x-on:notifications-changed.window="$wire.$refresh()"
                // No need to do anything here as the event is already dispatched by the store
            });

            // Listen for confirmation events (CSP-safe pattern)
            this._confirmListener = (e) => this.handleConfirmedAction(e.detail);
            window.addEventListener(
                'notifications:action-confirmed',
                this._confirmListener,
            );
        },

        destroy() {
            if (this.unsubscribe) {
                this.unsubscribe();
                this.unsubscribe = null;
            }
            if (this._confirmListener) {
                window.removeEventListener(
                    'notifications:action-confirmed',
                    this._confirmListener,
                );
                this._confirmListener = null;
            }
        },

        /**
         * Open confirm modal for clearing all notifications (CSP-safe)
         * @param {string} title - Modal title
         * @param {string} message - Modal message
         */
        openConfirmClearAll(title, message) {
            window.dispatchEvent(
                new CustomEvent('confirm-modal', {
                    detail: {
                        title: title,
                        message: message,
                        confirmEvent: 'notifications:action-confirmed',
                        confirmData: { action: 'clearAll' },
                    },
                    bubbles: true,
                }),
            );
        },

        /**
         * Open confirm modal for deleting a notification (CSP-safe)
         * @param {string} notificationId - ID of notification to delete
         * @param {string} title - Modal title
         * @param {string} message - Modal message
         */
        openConfirmDelete(notificationId, title, message) {
            this.pendingDeleteId = notificationId;
            window.dispatchEvent(
                new CustomEvent('confirm-modal', {
                    detail: {
                        title: title,
                        message: message,
                        confirmEvent: 'notifications:action-confirmed',
                        confirmData: { action: 'delete', id: notificationId },
                    },
                    bubbles: true,
                }),
            );
        },

        /**
         * Handle confirmed action from confirm modal (CSP-safe callback)
         * @param {Object} data - Action data from confirmation
         */
        handleConfirmedAction(data) {
            if (!data || !this.$wire) {
                return;
            }

            if (data.action === 'clearAll') {
                this.$wire.clearAll();
            } else if (data.action === 'delete' && data.id) {
                this.$wire.delete(data.id);
            }

            this.pendingDeleteId = null;
        },
    };
}

/**
 * Notification Dropdown Alpine.js Data Component
 * Manages notification dropdown state and updates
 */
export function notificationDropdown() {
    return {
        unsubscribe: null,
        isOpen: false,
        wasOpened: false,

        init() {
            if (!window.Alpine) {
                console.error(
                    '[Notification Dropdown] Alpine.js is not available',
                );
                return;
            }

            const store = window.Alpine.store('notifications');
            if (!store) {
                console.error(
                    '[Notification Dropdown] Notifications store is not available',
                );
                return;
            }

            this.unsubscribe = store.subscribe((eventName) => {
                if (eventName !== 'notification.changed') {
                    return;
                }

                // The refresh logic is handled by the template's event listener
            });
        },

        async close() {
            if (this.wasOpened) {
                // Check if Livewire component is still mounted before calling wire methods
                // $wire.__instance is undefined when the component has been destroyed (e.g., after navigation)
                if (this.$wire && this.$wire.__instance && typeof this.$wire.markVisibleAsRead === 'function') {
                    try {
                        await this.$wire.markVisibleAsRead();
                    } catch (e) {
                        // Component may have been unmounted during navigation, ignore error
                    }
                }
                this.wasOpened = false;
            }
            this.isOpen = false;
        },

        open() {
            this.isOpen = true;
            this.wasOpened = true;
        },

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        },

        destroy() {
            if (this.unsubscribe) {
                this.unsubscribe();
                this.unsubscribe = null;
            }
        },
    };
}
