/**
 * Alpine.js Store: Notifications
 *
 * Centralized store for managing notification subscriptions and real-time updates.
 * Handles Echo channel subscriptions, event listeners, and notification state.
 */

/**
 * Creates the notifications store for Alpine.js
 * @returns {Object} Alpine store object
 */
export function createNotificationsStore() {
    return {
        config: {
            userUuid: null,
            teamUuids: [],
            sessionId: null,
        },
        initialized: false,

        // Private state
        _listeners: [],
        _subscribedChannels: new Set(),
        _subscribers: new Set(),
        _pendingNotificationsProcessed: false,

        /**
         * Initialize the store with configuration
         * @param {Object} config - Configuration object with userUuid and teamUuids
         */
        init(config) {
            if (!config) {
                return;
            }

            this.config.userUuid = config?.userUuid || null;
            this.config.teamUuids = Array.isArray(config?.teamUuids)
                ? config?.teamUuids
                : [];
            this.config.sessionId = config?.sessionId || null;
            this.initialized = true;

            this._ensureEchoListeners();
        },

        /**
         * Subscribe to store events
         * @param {Function} callback - Callback function to receive events
         * @returns {Function} Unsubscribe function
         */
        subscribe(callback) {
            if (typeof callback !== "function") {
                console.error(
                    "[Notifications Store] Subscribe callback must be a function"
                );
                return () => {};
            }

            this._subscribers.add(callback);
            this._ensureEchoListeners();

            return () => {
                this._subscribers.delete(callback);
            };
        },

        /**
         * Emit event to all subscribers
         * @private
         * @param {string} eventName - Event name
         * @param {Object} payload - Event payload
         */
        _emit(eventName, payload) {
            if (eventName === "notification.changed") {
                window.dispatchEvent(
                    new CustomEvent("notifications-changed", {
                        detail: payload,
                        bubbles: true,
                    })
                );
            }

            this._subscribers.forEach((callback) => {
                try {
                    callback(eventName, payload);
                } catch (error) {
                    // Component not found errors are expected during Livewire navigation
                    // Silently ignore them - they occur when components are removed during navigation
                    const errorMessage = error?.message || String(error || "");
                    if (errorMessage.includes("Component not found")) {
                        return;
                    }
                    console.error(
                        "[Notifications Store] Error in subscriber callback:",
                        error
                    );
                }
            });
        },

        /**
         * Ensure Echo listeners are set up
         * @private
         */
        _ensureEchoListeners() {
            if (!this.initialized) {
                return;
            }

            if (typeof window.Echo === "undefined") {
                return;
            }

            const handleToast = (event) => {
                this._emit("toast.received", event);
            };

            const handleNotification = (event) => {
                this._emit("notification.changed", event);
            };

            // In auth context (no userUuid), only subscribe to session channel
            if (!this.config.userUuid) {
                // Only subscribe to session channel for non-authenticated users
                // Session channel is PUBLIC (session ID acts as security mechanism)
                if (this.config.sessionId) {
                    const sessionChannel = `public-notifications.session.${this.config.sessionId}`;
                    this._subscribeToPublicChannel(sessionChannel, {
                        toast: handleToast,
                    });
                }
                return;
            }

            // For authenticated users, subscribe to all channels
            // Subscribe to user channel
            this._subscribeToChannel(
                `private-notifications.user.${this.config.userUuid}`,
                {
                    toast: handleToast,
                    notification: handleNotification,
                }
            );

            // Subscribe to all team channels
            if (this.config.teamUuids && this.config.teamUuids.length > 0) {
                this.config.teamUuids.forEach((teamUuid) => {
                    if (!teamUuid) {
                        return;
                    }

                    this._subscribeToChannel(
                        `private-notifications.team.${teamUuid}`,
                        {
                            toast: handleToast,
                        }
                    );
                });
            }

            // Subscribe to global channel
            this._subscribeToChannel("private-notifications.global", {
                toast: handleToast,
            });

            // Subscribe to session channel (for notifications after logout)
            // Session channel is PUBLIC (session ID acts as security mechanism)
            if (this.config.sessionId) {
                this._subscribeToPublicChannel(
                    `public-notifications.session.${this.config.sessionId}`,
                    {
                        toast: handleToast,
                    }
                );
            }
        },

        /**
         * Subscribe to a specific Echo private channel
         * @private
         * @param {string} channelName - Channel name
         * @param {Object} handlers - Event handlers object
         */
        _subscribeToChannel(channelName, handlers) {
            if (this._subscribedChannels.has(channelName)) {
                return;
            }

            try {
                const channel = window.Echo.private(channelName);
                const listener = channel;

                // Some broadcasters require leading dot; support both for resilience
                if (handlers.toast) {
                    listener.listen(".toast.received", handlers.toast);
                    listener.listen("toast.received", handlers.toast);
                }

                if (handlers.notification) {
                    listener.listen(
                        ".notification.changed",
                        handlers.notification
                    );
                    listener.listen(
                        "notification.changed",
                        handlers.notification
                    );
                }

                this._listeners.push(listener);
                this._subscribedChannels.add(channelName);
            } catch (error) {
                console.error(
                    `[Notifications Store] Error subscribing to channel ${channelName}:`,
                    error
                );
            }
        },

        /**
         * Subscribe to a specific Echo public channel
         * @private
         * @param {string} channelName - Channel name
         * @param {Object} handlers - Event handlers object
         */
        _subscribeToPublicChannel(channelName, handlers) {
            if (this._subscribedChannels.has(channelName)) {
                return;
            }

            try {
                const channel = window.Echo.channel(channelName);
                const listener = channel;

                // Some broadcasters require leading dot; support both for resilience
                if (handlers.toast) {
                    listener.listen(".toast.received", handlers.toast);
                    listener.listen("toast.received", handlers.toast);
                }

                if (handlers.notification) {
                    listener.listen(
                        ".notification.changed",
                        handlers.notification
                    );
                    listener.listen(
                        "notification.changed",
                        handlers.notification
                    );
                }

                this._listeners.push(listener);
                this._subscribedChannels.add(channelName);
            } catch (error) {
                console.error(
                    `[Notifications Store] Error subscribing to channel ${channelName}:`,
                    error
                );
            }
        },

        /**
         * Cleanup all subscriptions
         * @private
         */
        _cleanup() {
            // Cleanup Echo listeners
            this._listeners.forEach((listener) => {
                try {
                    if (
                        listener &&
                        typeof listener.stopListening === "function"
                    ) {
                        listener.stopListening();
                    }
                } catch (error) {
                    console.error(
                        "[Notifications Store] Error cleaning up listener:",
                        error
                    );
                }
            });

            this._listeners = [];
            this._subscribedChannels.clear();
            this._subscribers.clear();
        },
    };
}
