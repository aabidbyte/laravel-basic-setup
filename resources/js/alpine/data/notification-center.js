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

        /**
         * Mark notification as read after a delay (CSP-safe)
         * Used by x-intersect for viewport-based auto mark-as-read
         * @param {string} notificationId - ID of notification to mark as read
         * @param {number} delayMs - Delay in milliseconds (default: 3000)
         */
        delayedMarkAsRead(notificationId, delayMs = 3000) {
            setTimeout(() => {
                if (this.$wire) {
                    this.$wire.markAsRead(notificationId);
                }
            }, delayMs);
        },
    };
}

/**
 * Notification Dropdown Trigger Alpine.js Data Component
 * Manages the static trigger button with badge (always visible)
 * @param {number} initialCount - Initial unread count from server
 */
export function notificationDropdownTrigger(initialCount = 0) {
    return {
        isOpen: false,
        wasOpened: false,
        unreadCount: initialCount,
        _countUpdateListener: null,
        _notificationChangedListener: null,
        // Track pending changes that haven't been confirmed by server yet
        _pendingDelta: 0,
        _lastServerCount: initialCount,

        init() {
            // Listen for accurate count from the content component (source of truth)
            this._countUpdateListener = (e) => {
                if (typeof e.detail?.count === 'number') {
                    // Server count is source of truth - reset pending delta
                    this._lastServerCount = e.detail.count;
                    this._pendingDelta = 0;
                    this.unreadCount = e.detail.count;
                }
            };
            window.addEventListener(
                'notification-dropdown:count-updated',
                this._countUpdateListener,
            );

            // Listen for notification changes for optimistic updates
            // When content isn't mounted, this provides real-time feedback
            this._notificationChangedListener = (e) => {
                const action = e.detail?.action;
                if (action === 'created') {
                    // Optimistically increment
                    this._pendingDelta++;
                    this.unreadCount =
                        this._lastServerCount + this._pendingDelta;
                } else if (action === 'deleted' || action === 'forceDeleted') {
                    // Optimistically decrement
                    this._pendingDelta--;
                    this.unreadCount = Math.max(
                        0,
                        this._lastServerCount + this._pendingDelta,
                    );
                } else if (action === 'updated') {
                    // Notification was updated (e.g., marked as read)
                    // Decrement if we have unread notifications
                    if (this.unreadCount > 0) {
                        this._pendingDelta--;
                        this.unreadCount = Math.max(
                            0,
                            this._lastServerCount + this._pendingDelta,
                        );
                    }
                }
            };
            window.addEventListener(
                'notifications-changed',
                this._notificationChangedListener,
            );
        },

        async close() {
            if (this.wasOpened) {
                // Dispatch event to content component to mark as read
                window.dispatchEvent(
                    new CustomEvent('notification-dropdown:close'),
                );
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
            if (this._countUpdateListener) {
                window.removeEventListener(
                    'notification-dropdown:count-updated',
                    this._countUpdateListener,
                );
                this._countUpdateListener = null;
            }
            if (this._notificationChangedListener) {
                window.removeEventListener(
                    'notifications-changed',
                    this._notificationChangedListener,
                );
                this._notificationChangedListener = null;
            }
        },
    };
}

/**
 * Notification Dropdown Content Alpine.js Data Component
 * Manages the Livewire content component (lazy loaded)
 * @param {Object} $wire - Livewire component instance
 */
export function notificationDropdownContent($wire) {
    return {
        _closeListener: null,
        _isDestroyed: false,

        init() {
            // Listen for close event from trigger to mark as read
            this._closeListener = async () => {
                // Skip if component has been destroyed (e.g., after navigation)
                if (this._isDestroyed) {
                    return;
                }

                // Only proceed if $wire still appears valid
                if (
                    !$wire ||
                    !$wire.__instance ||
                    typeof $wire.markVisibleAsRead !== 'function'
                ) {
                    return;
                }

                try {
                    await $wire.markVisibleAsRead();
                } catch (e) {
                    // "Component not found" is expected after wire:navigate
                    const errorMessage = e?.message || String(e || '');
                    if (errorMessage.includes('Component not found')) {
                        // Mark as destroyed to prevent future calls
                        this._isDestroyed = true;
                        return;
                    }
                    // Re-throw unexpected errors
                    console.error(
                        '[Notification Dropdown Content] Unexpected error:',
                        e,
                    );
                }
            };
            window.addEventListener(
                'notification-dropdown:close',
                this._closeListener,
            );
        },

        destroy() {
            this._isDestroyed = true;
            if (this._closeListener) {
                window.removeEventListener(
                    'notification-dropdown:close',
                    this._closeListener,
                );
                this._closeListener = null;
            }
        },
    };
}

/**
 * Notification Dropdown Alpine.js Data Component (Legacy - kept for compatibility)
 * @deprecated Use notificationDropdownTrigger and notificationDropdownContent instead
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
                if (
                    this.$wire &&
                    this.$wire.__instance &&
                    typeof this.$wire.markVisibleAsRead === 'function'
                ) {
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
