/**
 * Notification System - Main Entry Point
 *
 * Centralized notification management using Alpine.js stores and data components.
 * Handles real-time notifications via Laravel Echo, toast notifications, and notification center updates.
 *
 * Architecture:
 * - Stores: Alpine.store('notifications') - Manages Echo subscriptions and event broadcasting
 * - Data Components: Alpine.data() - Reusable components for toast center, notification center, dropdown
 * - Separation of Concerns: Each module handles specific functionality
 */

import { createNotificationsStore } from "./alpine/stores/notifications-store.js";
import { toastCenter, toastItem } from "./alpine/data/toast-center.js";
import {
    notificationCenter,
    notificationDropdown,
} from "./alpine/data/notification-center.js";

// Module-level initialization state
let storeRegistered = false;
let componentsRegistered = false;
let currentConfigKey = null;

/**
 * Register the notifications store with Alpine (idempotent)
 */
function registerNotificationsStore() {
    if (!window.Alpine || storeRegistered) {
        return;
    }

    window.Alpine.store("notifications", createNotificationsStore());
    storeRegistered = true;
}

/**
 * Register Alpine data components (idempotent)
 */
function registerAlpineDataComponents() {
    if (!window.Alpine || componentsRegistered) {
        return;
    }

    window.Alpine.data("toastCenter", toastCenter);
    window.Alpine.data("toastItem", toastItem);
    window.Alpine.data("notificationCenter", notificationCenter);
    window.Alpine.data("notificationDropdown", notificationDropdown);
    componentsRegistered = true;
}

/**
 * Initialize notifications from window configuration
 * Only initializes if config has changed (for Livewire navigation)
 */
function initializeNotifications() {
    if (!window.Alpine || !storeRegistered) {
        return;
    }

    const config = window.notificationRealtimeConfig;
    if (!config) {
        return;
    }

    // Create config key to detect changes
    const configKey = `${config?.userUuid || "null"}-${
        config?.sessionId || "null"
    }`;

    // Skip if same config is already initialized
    if (currentConfigKey === configKey) {
        return;
    }

    currentConfigKey = configKey;

    // Store config in Alpine store for reference
    window.Alpine.store("notificationRealtimeConfig", config);

    // Initialize notifications store with config
    const notificationsStore = window.Alpine.store("notifications");
    if (notificationsStore && typeof notificationsStore.init === "function") {
        try {
            notificationsStore.init(config);
        } catch (error) {
            console.error(
                "[Notification System] Error initializing store:",
                error
            );
        }
    }

    // Process pending notifications from session (fallback for redirects)
    // Only process once per config change
    if (
        config.pendingNotifications &&
        Array.isArray(config.pendingNotifications) &&
        config.pendingNotifications.length > 0
    ) {
        const store = window.Alpine.store("notifications");
        if (store && typeof store._emit === "function") {
            // Reset pending notifications flag when config changes
            store._pendingNotificationsProcessed = false;

            setTimeout(() => {
                if (store && !store._pendingNotificationsProcessed) {
                    store._pendingNotificationsProcessed = true;
                    config.pendingNotifications.forEach((notification) => {
                        store._emit("toast.received", notification);
                    });
                }
            }, 100);
        }
    }
}

/**
 * Single initialization function that handles all setup
 */
function initializeNotificationSystem() {
    registerNotificationsStore();
    registerAlpineDataComponents();
    initializeNotifications();
}

// Initialize when Alpine is ready
document.addEventListener("alpine:init", initializeNotificationSystem);

// Initialize immediately if Alpine is already available
if (window.Alpine) {
    initializeNotificationSystem();
}

// Re-initialize after Livewire navigation (config may have changed)
document.addEventListener("livewire:navigated", initializeNotifications);
