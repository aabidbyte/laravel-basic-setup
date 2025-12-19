function createNotificationsStore() {
    return {
        config: {
            userUuid: null,
            teamUuids: [],
        },
        initialized: false,

        _listeners: [],
        _subscribedChannels: new Set(),
        _subscribers: new Set(),

        init(config) {
            this.config.userUuid = config?.userUuid || null;
            this.config.teamUuids = Array.isArray(config?.teamUuids)
                ? config.teamUuids
                : [];
            this.initialized = true;

            this._ensureEchoListeners();
        },

        subscribe(callback) {
            this._subscribers.add(callback);
            this._ensureEchoListeners();

            return () => {
                this._subscribers.delete(callback);
            };
        },

        _emit(eventName, payload) {
            if (eventName === "notification.changed") {
                window.dispatchEvent(
                    new CustomEvent("notifications-changed", {
                        detail: payload,
                    })
                );
            }

            this._subscribers.forEach((callback) =>
                callback(eventName, payload)
            );
        },

        _ensureEchoListeners() {
            if (!this.initialized) {
                return;
            }

            if (typeof window.Echo === "undefined") {
                return;
            }

            const handleToast = (e) => {
                this._emit("toast.received", e);
            };
            const handleNotification = (e) =>
                this._emit("notification.changed", e);

            // Subscribe to user channel
            if (this.config.userUuid) {
                const channelName = `private-notifications.user.${this.config.userUuid}`;
                if (!this._subscribedChannels.has(channelName)) {
                    const channel = window.Echo.private(channelName);

                    // Some broadcasters require the leading dot; some accept without.
                    // Keep both to be resilient.
                    const listener = channel
                        .listen(".toast.received", handleToast)
                        .listen("toast.received", handleToast)
                        .listen(".notification.changed", handleNotification);
                    listener.listen("notification.changed", handleNotification);
                    this._listeners.push(listener);
                    this._subscribedChannels.add(channelName);
                }
            }

            // Subscribe to all team channels
            if (this.config.teamUuids && this.config.teamUuids.length > 0) {
                this.config.teamUuids.forEach((teamUuid) => {
                    if (!teamUuid) {
                        return;
                    }

                    const channelName = `private-notifications.team.${teamUuid}`;
                    if (this._subscribedChannels.has(channelName)) {
                        return;
                    }

                    const listener = window.Echo.private(channelName).listen(
                        ".toast.received",
                        handleToast
                    );
                    this._listeners.push(listener);
                    this._subscribedChannels.add(channelName);
                });
            }

            // Subscribe to global channel
            const globalChannelName = "private-notifications.global";
            if (!this._subscribedChannels.has(globalChannelName)) {
                const globalListener = window.Echo.private(
                    globalChannelName
                ).listen(".toast.received", handleToast);
                this._listeners.push(globalListener);
                this._subscribedChannels.add(globalChannelName);
            }
        },
    };
}

function registerNotificationsStore() {
    if (!window.Alpine) {
        return;
    }

    // If Alpine already booted (or this file loaded late), register immediately.
    // If already registered, do nothing.
    const existingStore = window.Alpine.store("notifications");
    if (existingStore) {
        return;
    }

    window.Alpine.store("notifications", createNotificationsStore());
}

function initNotificationsFromWindowConfig() {
    if (!window.Alpine) {
        return;
    }

    const config = window.notificationRealtimeConfig;

    if (!config) {
        return;
    }

    // Always refresh this store (avoid stale config across navigations).
    window.Alpine.store("notificationRealtimeConfig", config);

    const notificationsStore = window.Alpine.store("notifications");
    if (notificationsStore && typeof notificationsStore.init === "function") {
        notificationsStore.init(config);
    }
}

// Register before Alpine boots...
document.addEventListener("alpine:init", registerNotificationsStore);

document.addEventListener("alpine:init", () => {
    if (!window.Alpine) {
        return;
    }

    initNotificationsFromWindowConfig();
});

// ...and also register immediately if Alpine is already available.
registerNotificationsStore();

// If Alpine is already available (ex: loaded after alpine:init), still init.
initNotificationsFromWindowConfig();

// Livewire Navigate can swap pages without full reload; re-init after navigation.
document.addEventListener("livewire:navigated", () => {
    initNotificationsFromWindowConfig();
});

function toastCenter() {
    return {
        toasts: [],
        unsubscribe: null,

        init() {
            if (!window.Alpine) {
                return;
            }

            // Only subscribe if we haven't already subscribed (prevents duplicate subscriptions)
            if (this.unsubscribe) {
                return;
            }

            const store = window.Alpine.store("notifications");
            this.unsubscribe = store.subscribe((eventName, payload) => {
                if (eventName !== "toast.received") {
                    return;
                }

                this.addToast(payload);
            });
        },

        destroy() {
            if (this.unsubscribe) {
                this.unsubscribe();
                this.unsubscribe = null;
            }
        },

        addToast(data) {
            const type = data.type || "success";
            const iconName = this.getIconName(type);
            const toast = {
                id: Date.now() + Math.random(),
                timestamp: Date.now(),
                title: data.title || "",
                subtitle: data.subtitle || null,
                content: data.content || null,
                type: type,
                iconName: iconName,
                iconHtml: data.iconHtml || null, // Server-rendered icon HTML
                position: data.position || "top-right",
                link: data.link || null,
            };

            this.toasts.push(toast);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.removeToast(toast.id);
            }, 5000);
        },

        removeToast(id) {
            const index = this.toasts.findIndex((t) => t.id === id);
            if (index > -1) {
                this.toasts.splice(index, 1);
            }
        },

        handleClick(toast) {
            if (toast.link) {
                window.location.href = toast.link;
            }
        },

        getIconName(type) {
            const icons = {
                success: "check-circle",
                info: "information-circle",
                warning: "exclamation-triangle",
                error: "x-circle",
                classic: "bell",
            };
            const iconName = icons[type] || "bell";
            return iconName;
        },

        getIconClasses(type) {
            const classes = {
                success: "text-success",
                info: "text-info",
                warning: "text-warning",
                error: "text-error",
                classic: "text-base-content",
            };
            return classes[type] || "text-base-content";
        },

        getIconSvg(iconName) {
            // Fallback client-side icon rendering (only used if server-rendered iconHtml is not provided)
            const iconPaths = {
                "check-circle":
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                "information-circle":
                    '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9-3.75h.008v.008H12V8.25Z"/>',
                "exclamation-triangle":
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
                "x-circle":
                    '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                bell: '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>',
            };
            const path = iconPaths[iconName] || iconPaths["bell"];
            return `<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">${path}</svg>`;
        },
    };
}

function notificationCenter($wire) {
    return {
        unsubscribe: null,

        init() {
            if (!window.Alpine) {
                return;
            }

            const store = window.Alpine.store("notifications");

            this.unsubscribe = store.subscribe((eventName) => {
                if (eventName !== "notification.changed") {
                    return;
                }

                if ($wire && typeof $wire.$refresh === "function") {
                    $wire.$refresh();
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

function notificationDropdown($wire) {
    return {
        unsubscribe: null,
        isOpen: false,
        wasOpened: false,

        init() {
            if (!window.Alpine) {
                return;
            }

            const store = window.Alpine.store("notifications");

            this.unsubscribe = store.subscribe((eventName) => {
                if (eventName !== "notification.changed") {
                    return;
                }

                if ($wire && typeof $wire.$refresh === "function") {
                    $wire.$refresh();
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

// Make functions available globally for Alpine.js
window.toastCenter = toastCenter;
window.notificationCenter = notificationCenter;
window.notificationDropdown = notificationDropdown;
