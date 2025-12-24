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

                // Use reactive $wire - it's automatically updated by Livewire
                // After navigation, if component exists, $wire will be available
                this.refreshIfAvailable();
            });
        },

        /**
         * Safely refresh the Livewire component if it exists
         * Uses reactive $wire which is automatically updated by Livewire
         */
        refreshIfAvailable() {
            // $wire is reactive - Livewire handles the lifecycle
            // After navigation, if component exists, $wire is automatically available
            // If component was removed, $wire is null/undefined
            if (!$wire || typeof $wire.$refresh !== "function") {
                return;
            }

            // Additional validation: check if component still exists in Livewire registry
            if (window.Livewire && $wire.__instance?.id) {
                const component = window.Livewire.find($wire.__instance.id);
                if (!component) {
                    // Component was removed from DOM but $wire still exists
                    return;
                }
            }

            // Safe to refresh - component exists and is valid
            $wire.$refresh();
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

                // Use reactive $wire - it's automatically updated by Livewire
                // After navigation, if component exists, $wire will be available
                this.refreshIfAvailable();
            });
        },

        /**
         * Safely refresh the Livewire component if it exists
         * Uses reactive $wire which is automatically updated by Livewire
         */
        refreshIfAvailable() {
            // $wire is reactive - Livewire handles the lifecycle
            // After navigation, if component exists, $wire is automatically available
            // If component was removed, $wire is null/undefined
            if (!$wire || typeof $wire.$refresh !== "function") {
                return;
            }

            // Additional validation: check if component still exists in Livewire registry
            if (window.Livewire && $wire.__instance?.id) {
                const component = window.Livewire.find($wire.__instance.id);
                if (!component) {
                    // Component was removed from DOM but $wire still exists
                    return;
                }
            }

            // Safe to refresh - component exists and is valid
            $wire.$refresh();
        },

        destroy() {
            if (this.unsubscribe) {
                this.unsubscribe();
                this.unsubscribe = null;
            }
        },
    };
}
