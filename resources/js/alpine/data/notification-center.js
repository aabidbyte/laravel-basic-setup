/**
 * Alpine.js Data Components: Notification Center
 *
 * Handles real-time notification updates for notification center and dropdown components.
 */

/**
 * Notification Center Alpine.js Data Component
 * Manages notification center page updates
 */
export function notificationCenter($wire) {
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

                // Refresh Livewire component if available
                if ($wire && typeof $wire.$refresh === "function") {
                    try {
                        $wire.$refresh();
                    } catch (error) {
                        // Component may have been removed (e.g., during navigation)
                        // Silently handle this - it's expected behavior
                        const errorMessage =
                            error?.message || String(error || "");
                        if (errorMessage.includes("Component not found")) {
                            return;
                        }
                        console.error(
                            "[Notification Center] Error refreshing component:",
                            error
                        );
                    }
                }
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
export function notificationDropdown($wire) {
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

                // Refresh Livewire component if available
                if ($wire && typeof $wire.$refresh === "function") {
                    try {
                        $wire.$refresh();
                    } catch (error) {
                        // Component may have been removed (e.g., during navigation)
                        // Silently handle this - it's expected behavior
                        const errorMessage =
                            error?.message || String(error || "");
                        if (errorMessage.includes("Component not found")) {
                            return;
                        }
                        console.error(
                            "[Notification Dropdown] Error refreshing component:",
                            error
                        );
                    }
                }
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
