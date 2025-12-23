/**
 * Alpine.js Data Component: Toast Center
 *
 * Manages toast notifications display, animations, and auto-dismiss behavior.
 * Uses Alpine.data() pattern for reusability and maintainability.
 */

/**
 * Configuration constants
 */
const DEFAULT_DISPLAY_DURATION = 5000; // 5 seconds
const PROGRESS_UPDATE_INTERVAL = 16; // ~60fps
const NOTIFICATION_SOUND_URL =
    'https://res.cloudinary.com/ds8pgw1pf/video/upload/v1728571480/penguinui/component-assets/sounds/ding.mp3';

/**
 * Toast type configuration
 */
const TOAST_CONFIG = {
    success: {
        border: 'border-success',
        bgOverlay: 'bg-success/10',
        iconBg: 'bg-success/15',
        iconText: 'text-success',
        titleText: 'text-success',
        linkText: 'text-success hover:text-success/80 focus:outline-success',
        progressColor: 'bg-success',
    },
    info: {
        border: 'border-info',
        bgOverlay: 'bg-info/10',
        iconBg: 'bg-info/15',
        iconText: 'text-info',
        titleText: 'text-info',
        linkText: 'text-info hover:text-info/80 focus:outline-info',
        progressColor: 'bg-info',
    },
    warning: {
        border: 'border-warning',
        bgOverlay: 'bg-warning/10',
        iconBg: 'bg-warning/15',
        iconText: 'text-warning',
        titleText: 'text-warning',
        linkText: 'text-warning hover:text-warning/80 focus:outline-warning',
        progressColor: 'bg-warning',
    },
    error: {
        border: 'border-error',
        bgOverlay: 'bg-error/10',
        iconBg: 'bg-error/15',
        iconText: 'text-error',
        titleText: 'text-error',
        linkText: 'text-error hover:text-error/80 focus:outline-error',
        progressColor: 'bg-error',
    },
    classic: {
        border: 'border-base-300',
        bgOverlay: 'bg-base-200/50',
        iconBg: 'bg-base-300/15',
        iconText: 'text-base-content',
        titleText: 'text-base-content',
        linkText: 'text-primary hover:text-primary/80 focus:outline-primary',
        progressColor: 'bg-primary',
    },
};

/**
 * Toast position configuration
 */
const POSITION_CONFIG = {
    'top-right': 'top-0 right-0',
    'top-left': 'top-0 left-0',
    'top-center': 'top-0 left-1/2 -translate-x-1/2',
    'bottom-right': 'bottom-0 right-0',
    'bottom-left': 'bottom-0 left-0',
    'bottom-center': 'bottom-0 left-1/2 -translate-x-1/2',
    center: 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
};

/**
 * Get toast type configuration
 * @param {string} type - Toast type
 * @returns {Object} Toast configuration object
 */
function getToastConfig(type) {
    return TOAST_CONFIG[type] || TOAST_CONFIG.classic;
}

/**
 * Get toast position classes
 * @param {string} position - Toast position
 * @returns {string} Position classes
 */
function getToastPosition(position) {
    return POSITION_CONFIG[position] || POSITION_CONFIG['top-right'];
}

/**
 * Sanitize toast data to prevent XSS
 * @param {Object} data - Raw toast data
 * @returns {Object} Sanitized toast data
 */
function sanitizeToastData(data) {
    return {
        title: data.title || '',
        subtitle: data.subtitle || null,
        content: data.content || null,
        type: data.type || 'success',
        position: data.position || 'top-right',
        link: data.link || null,
        enableSound: data.enableSound !== undefined ? data.enableSound : true,
        iconHtml: data.iconHtml || null,
    };
}

/**
 * Create a unique toast ID
 * @returns {string} Unique toast ID
 */
function createToastId() {
    return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
}

/**
 * Toast Center Alpine.js Data Component
 */
export function toastCenter() {
    return {
        toasts: [],
        unsubscribe: null,
        displayDuration: DEFAULT_DISPLAY_DURATION,

        init() {
            if (!window.Alpine) {
                console.error('[Toast Center] Alpine.js is not available');
                return;
            }

            // Only subscribe if we haven't already (idempotent subscription)
            if (this.unsubscribe) {
                return;
            }

            const store = window.Alpine.store('notifications');
            if (!store) {
                console.error('[Toast Center] Notifications store is not available');
                return;
            }

            this.unsubscribe = store.subscribe((eventName, payload) => {
                if (eventName !== 'toast.received') {
                    return;
                }

                this.addToast(payload);
            });
        },

        destroy() {
            // Cleanup subscription
            if (this.unsubscribe) {
                this.unsubscribe();
                this.unsubscribe = null;
            }

            // Clear all timeouts and intervals for remaining toasts
            this.toasts.forEach((toast) => {
                if (toast.timeout) {
                    clearTimeout(toast.timeout);
                }
                if (toast.progressInterval) {
                    clearInterval(toast.progressInterval);
                }
            });

            this.toasts = [];
        },

        /**
         * Add a new toast notification
         * @param {Object} data - Toast data
         */
        addToast(data) {
            const sanitized = sanitizeToastData(data);
            const typeConfig = getToastConfig(sanitized.type);

            const toast = {
                id: createToastId(),
                timestamp: Date.now(),
                title: sanitized.title,
                subtitle: sanitized.subtitle,
                content: sanitized.content,
                type: sanitized.type,
                iconHtml: sanitized.iconHtml,
                position: sanitized.position,
                link: sanitized.link,
                typeClasses: typeConfig,
                progressColor: typeConfig.progressColor,
                enableSound: sanitized.enableSound,
                timeout: null,
                progressInterval: null,
            };

            this.toasts.push(toast);

            // Play sound if enabled
            if (sanitized.enableSound) {
                this._playSound();
            }
        },

        /**
         * Play notification sound
         * @private
         */
        _playSound() {
            try {
                const notificationSound = new Audio(NOTIFICATION_SOUND_URL);
                notificationSound.play().catch((error) => {
                    // Silently fail for sound playback (user might have disabled audio)
                    if (error.name !== 'NotAllowedError') {
                        console.error('[Toast Center] Error playing sound:', error);
                    }
                });
            } catch (error) {
                console.error('[Toast Center] Error creating sound:', error);
            }
        },

        /**
         * Get container position based on first toast
         * @returns {string} Position classes
         */
        getContainerPosition() {
            if (this.toasts.length === 0) {
                return getToastPosition('top-right');
            }

            // Use position of the first (most recent) toast
            return getToastPosition(this.toasts[0].position);
        },
    };
}

/**
 * Toast Item Alpine.js Data Component
 * Handles individual toast state, progress, and lifecycle
 */
export function toastItem(toast, toasts, displayDuration) {
    return {
        isVisible: false,
        timeout: null,
        progress: 100,
        progressInterval: null,
        startTime: null,
        elapsedTime: 0,
        displayDuration: displayDuration || DEFAULT_DISPLAY_DURATION,

        init() {
            // Show toast after Alpine finishes rendering
            this.$nextTick(() => {
                this.isVisible = true;
                this.startProgress();
                this._scheduleDismiss();
            });
        },

        /**
         * Remove toast from array
         */
        removeToast() {
            const index = toasts.findIndex((t) => t.id === toast.id);
            if (index > -1) {
                this._cleanup();
                toasts.splice(index, 1);
            }
        },

        /**
         * Handle toast click (navigate if link exists)
         */
        handleClick() {
            if (toast.link) {
                try {
                    window.location.href = toast.link;
                } catch (error) {
                    console.error('[Toast Item] Error navigating to link:', error);
                }
            }
        },

        /**
         * Start progress bar animation
         */
        startProgress() {
            this.startTime = Date.now();
            this.elapsedTime = 0;
            this.progress = 100;

            this.progressInterval = setInterval(() => {
                if (!this.isVisible) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                    return;
                }

                this.elapsedTime = Date.now() - this.startTime;
                this.progress = Math.max(
                    0,
                    100 - (this.elapsedTime / this.displayDuration) * 100
                );

                if (this.progress <= 0) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }
            }, PROGRESS_UPDATE_INTERVAL);
        },

        /**
         * Pause progress bar
         */
        pauseProgress() {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },

        /**
         * Resume progress bar
         */
        resumeProgress() {
            if (this.isVisible && !this.progressInterval) {
                this.startTime = Date.now() - this.elapsedTime;
                this.startProgress();
            }
        },

        /**
         * Schedule toast dismissal
         * @private
         */
        _scheduleDismiss() {
            this.timeout = setTimeout(() => {
                this.isVisible = false;
                // Remove after transition completes
                setTimeout(() => {
                    this.removeToast();
                }, 300); // Match transition duration
            }, this.displayDuration);
        },

        /**
         * Cleanup all timers and intervals
         * @private
         */
        _cleanup() {
            if (this.timeout) {
                clearTimeout(this.timeout);
                this.timeout = null;
            }

            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },
    };
}

