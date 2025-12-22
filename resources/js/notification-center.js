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
        displayDuration: 5000, // Duration in milliseconds for auto-dismiss

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
            const typeClasses = this.getTypeClasses(type);
            const position = data.position || "top-right";
            const enableSound =
                data.enableSound !== undefined ? data.enableSound : true;
            const toast = {
                id: Date.now() + Math.random(),
                timestamp: Date.now(),
                title: data.title || "",
                subtitle: data.subtitle || null,
                content: data.content || null,
                type: type,
                iconHtml: data.iconHtml || null, // Server-rendered icon HTML
                position: position,
                link: data.link || null,
                typeClasses: typeClasses, // Pre-computed type classes
                progressColor: this.getProgressColor(type), // Progress bar color
                enableSound: enableSound,
            };

            this.toasts.push(toast);

            // Play sound if enabled
            if (enableSound) {
                this.playSound();
            }
        },

        playSound() {
            try {
                const notificationSound = new Audio(
                    "https://res.cloudinary.com/ds8pgw1pf/video/upload/v1728571480/penguinui/component-assets/sounds/ding.mp3"
                );
                notificationSound.play().catch((error) => {
                    console.error("Error playing the sound:", error);
                });
            } catch (error) {
                console.error("Error creating sound:", error);
            }
        },

        getProgressColor(type) {
            const colorMap = {
                success: "bg-success",
                info: "bg-info",
                warning: "bg-warning",
                error: "bg-error",
                classic: "bg-primary",
            };
            return colorMap[type] || colorMap.classic;
        },

        getTypeClasses(type) {
            const typeMap = {
                success: {
                    border: "border-success",
                    bgOverlay: "bg-success/10",
                    iconBg: "bg-success/15",
                    iconText: "text-success",
                    titleText: "text-success",
                    linkText:
                        "text-success hover:text-success/80 focus:outline-success",
                },
                info: {
                    border: "border-info",
                    bgOverlay: "bg-info/10",
                    iconBg: "bg-info/15",
                    iconText: "text-info",
                    titleText: "text-info",
                    linkText: "text-info hover:text-info/80 focus:outline-info",
                },
                warning: {
                    border: "border-warning",
                    bgOverlay: "bg-warning/10",
                    iconBg: "bg-warning/15",
                    iconText: "text-warning",
                    titleText: "text-warning",
                    linkText:
                        "text-warning hover:text-warning/80 focus:outline-warning",
                },
                error: {
                    border: "border-error",
                    bgOverlay: "bg-error/10",
                    iconBg: "bg-error/15",
                    iconText: "text-error",
                    titleText: "text-error",
                    linkText:
                        "text-error hover:text-error/80 focus:outline-error",
                },
                classic: {
                    border: "border-base-300",
                    bgOverlay: "bg-base-200/50",
                    iconBg: "bg-base-300/15",
                    iconText: "text-base-content",
                    titleText: "text-base-content",
                    linkText:
                        "text-primary hover:text-primary/80 focus:outline-primary",
                },
            };
            return typeMap[type] || typeMap.classic;
        },

        getToastPosition(position) {
            const positionMap = {
                "top-right": "top-0 right-0",
                "top-left": "top-0 left-0",
                "top-center": "top-0 left-1/2 -translate-x-1/2",
                "bottom-right": "bottom-0 right-0",
                "bottom-left": "bottom-0 left-0",
                "bottom-center": "bottom-0 left-1/2 -translate-x-1/2",
                center: "top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2",
            };
            return positionMap[position] || positionMap["top-right"];
        },

        getContainerPosition() {
            if (this.toasts.length === 0) {
                return "top-0 right-0";
            }
            // Use the position of the first toast (most recent)
            return this.getToastPosition(this.toasts[0].position);
        },
    };
}

function toastItem(toast, toasts, displayDuration) {
    return {
        isVisible: false,
        timeout: null,
        progress: 100,
        progressInterval: null,
        startTime: null,
        elapsedTime: 0,
        displayDuration: displayDuration || 5000,
        removeToast() {
            const idx = toasts.findIndex((t) => t.id === toast.id);
            if (idx > -1) {
                clearTimeout(this.timeout);
                clearInterval(this.progressInterval);
                toasts.splice(idx, 1);
            }
        },
        handleClick() {
            if (toast.link) {
                window.location.href = toast.link;
            }
        },
        startProgress() {
            const self = this;
            self.startTime = Date.now();
            self.elapsedTime = 0;
            self.progress = 100;
            self.progressInterval = setInterval(() => {
                if (!self.isVisible) {
                    clearInterval(self.progressInterval);
                    return;
                }
                self.elapsedTime = Date.now() - self.startTime;
                self.progress = Math.max(
                    0,
                    100 - (self.elapsedTime / self.displayDuration) * 100
                );
                if (self.progress <= 0) {
                    clearInterval(self.progressInterval);
                }
            }, 16); // ~60fps
        },
        pauseProgress() {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },
        resumeProgress() {
            if (this.isVisible && !this.progressInterval) {
                this.startTime = Date.now() - this.elapsedTime;
                this.startProgress();
            }
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
window.toastItem = toastItem;
window.notificationCenter = notificationCenter;
window.notificationDropdown = notificationDropdown;
