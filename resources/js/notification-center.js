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

import { createNotificationsStore } from './alpine/stores/notifications-store.js';
import { toastCenter, toastItem } from './alpine/data/toast-center.js';
import {
    notificationCenter,
    notificationDropdown,
} from './alpine/data/notification-center.js';

/**
 * Register the notifications store with Alpine
 * Must be called before Alpine.start()
 */
function registerNotificationsStore() {
    if (!window.Alpine) {
        console.warn('[Notification System] Alpine.js not available, deferring store registration');
        return;
    }

    // Prevent duplicate registration
    const existingStore = window.Alpine.store('notifications');
    if (existingStore) {
        return;
    }

    window.Alpine.store('notifications', createNotificationsStore());
}

/**
 * Initialize notifications from window configuration
 * Called after Alpine is initialized and when config is available
 */
function initNotificationsFromWindowConfig() {
    if (!window.Alpine) {
        return;
    }

    const config = window.notificationRealtimeConfig;

    if (!config) {
        return;
    }

    // Store config in Alpine store for reference
    window.Alpine.store('notificationRealtimeConfig', config);

    // Initialize notifications store with config
    const notificationsStore = window.Alpine.store('notifications');
    if (notificationsStore && typeof notificationsStore.init === 'function') {
        try {
            notificationsStore.init(config);
        } catch (error) {
            console.error('[Notification System] Error initializing store:', error);
        }
    }
}

/**
 * Register Alpine data components
 * Makes them available globally for use in Blade templates
 */
function registerAlpineDataComponents() {
    if (!window.Alpine) {
        console.warn('[Notification System] Alpine.js not available, deferring component registration');
        return;
    }

    // Register data components using Alpine.data()
    // This is the recommended pattern for reusable Alpine components
    window.Alpine.data('toastCenter', toastCenter);
    window.Alpine.data('toastItem', toastItem);
    window.Alpine.data('notificationCenter', notificationCenter);
    window.Alpine.data('notificationDropdown', notificationDropdown);
}

// Register store and components before Alpine boots
document.addEventListener('alpine:init', () => {
    registerNotificationsStore();
    registerAlpineDataComponents();
    initNotificationsFromWindowConfig();
});

// Also register immediately if Alpine is already available
// (handles cases where this script loads after Alpine)
if (window.Alpine) {
    registerNotificationsStore();
    registerAlpineDataComponents();
    initNotificationsFromWindowConfig();
}

// Re-initialize after Livewire navigation
// Livewire can swap pages without full reload, so we need to re-init
document.addEventListener('livewire:navigated', () => {
    initNotificationsFromWindowConfig();
});
