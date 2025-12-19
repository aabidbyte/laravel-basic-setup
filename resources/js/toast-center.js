function toastCenter(userUuid, teamUuids) {
    return {
        toasts: [],
        userUuid: userUuid,
        teamUuids: teamUuids || [],
        listeners: [],

        init() {
            if (typeof window.Echo === "undefined") {
                console.warn(
                    "Echo is not available. Toast notifications will not work."
                );
                return;
            }

            // Subscribe to user channel
            if (this.userUuid) {
                const listener = window.Echo.private(
                    `private-notifications.user.${this.userUuid}`
                ).listen(".toast.received", (e) => {
                    this.addToast(e);
                });
                this.listeners.push(listener);
            }

            // Subscribe to all team channels
            if (this.teamUuids && this.teamUuids.length > 0) {
                this.teamUuids.forEach((teamUuid) => {
                    if (teamUuid) {
                        const listener = window.Echo.private(
                            `private-notifications.team.${teamUuid}`
                        ).listen(".toast.received", (e) => {
                            this.addToast(e);
                        });
                        this.listeners.push(listener);
                    }
                });
            }

            // Subscribe to global channel
            const globalListener = window.Echo.private(
                "private-notifications.global"
            ).listen(".toast.received", (e) => {
                this.addToast(e);
            });
            this.listeners.push(globalListener);
        },

        destroy() {
            // Stop all listeners when component is destroyed
            this.listeners.forEach((listener) => {
                if (listener && typeof listener.stop === "function") {
                    listener.stop();
                }
            });
            this.listeners = [];
        },

        addToast(data) {
            const type = data.type || "success";
            const toast = {
                id: Date.now() + Math.random(),
                timestamp: Date.now(),
                title: data.title || "",
                subtitle: data.subtitle || null,
                content: data.content || null,
                type: type,
                iconName: this.getIconName(type),
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
            return icons[type] || "bell";
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
    };
}

// Make toastCenter available globally for Alpine.js
window.toastCenter = toastCenter;
