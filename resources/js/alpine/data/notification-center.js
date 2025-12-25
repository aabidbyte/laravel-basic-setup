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

        init() {
            if (!window.Alpine) {
                console.error(
                    "[Notification Center] Alpine.js is not available"
                );
                return;
            }

            const store = window.Alpine.store("notifications");
            if (!store) {
                console.error(
                    "[Notification Center] Notifications store is not available"
                );
                return;
            }

            this.unsubscribe = store.subscribe((eventName) => {
                if (eventName !== "notification.changed") {
                    return;
                }

                // The refresh logic is handled by the template's event listener:
                // x-on:notifications-changed.window="$wire.$refresh()"
                // No need to do anything here as the event is already dispatched by the store
            });
        },

        destroy() {
            if (this.unsubscribe) {
                this.unsubscribe();
                this.unsubscribe = null;
            }
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
                    "[Notification Dropdown] Alpine.js is not available"
                );
                return;
            }

            const store = window.Alpine.store("notifications");
            if (!store) {
                console.error(
                    "[Notification Dropdown] Notifications store is not available"
                );
                return;
            }

            this.unsubscribe = store.subscribe((eventName) => {
                if (eventName !== "notification.changed") {
                    return;
                }

                // The refresh logic is handled by the template's event listener:
                // x-on:notifications-changed.window="$wire.$refresh()"
                // No need to do anything here as the event is already dispatched by the store
            });
        },

        destroy() {
            if (this.unsubscribe) {
                this.unsubscribe();
                this.unsubscribe = null;
            }
        },
    };
}
