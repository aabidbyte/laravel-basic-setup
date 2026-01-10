/**
 * Validation Error Scroller - CSP-Safe Alpine Data Component
 * Automatically scrolls to the first validation error when forms receive validation errors.
 * Self-registers as 'validationErrorScroller'
 *
 * @param {Object} config - Configuration options
 * @param {number} config.offset - Offset from top in pixels (default: 100)
 * @param {string} config.behavior - Scroll behavior: 'smooth' or 'auto' (default: 'smooth')
 * @param {string} config.block - Scroll alignment: 'start', 'center', 'end', 'nearest' (default: 'center')
 * @returns {Object} Alpine.js component
 */
export function validationErrorScroller(config = {}) {
    return {
        offset: config.offset || 100,
        behavior: config.behavior || 'smooth',
        block: config.block || 'center',
        hasScrolled: false,
        commitListener: null,

        init() {
            // Store the listener reference for cleanup
            this.commitListener = () => {
                this.scrollToFirstError();
            };

            // Listen for Livewire updates to detect validation errors
            this.$wire.$on('commit', this.commitListener);

            // Also check on mount in case errors are already present
            this.$nextTick(() => {
                this.scrollToFirstError();
            });
        },

        /**
         * Find and scroll to the first validation error in the DOM
         */
        scrollToFirstError() {
            // Reset scroll flag on each validation attempt
            this.hasScrolled = false;

            // Wait for DOM to update with error states
            this.$nextTick(() => {
                const errorElement = this.findFirstError();

                if (errorElement && !this.hasScrolled) {
                    this.scrollToElement(errorElement);
                    this.hasScrolled = true;
                }
            });
        },

        /**
         * Find the first error element in the DOM
         * Looks for elements with .input-error class (DaisyUI error state)
         *
         * @returns {HTMLElement|null} First error element or null
         */
        findFirstError() {
            // Find all elements with error class
            const errorInputs = document.querySelectorAll(
                '.input-error, .select-error, .textarea-error',
            );

            if (errorInputs.length === 0) {
                return null;
            }

            // Return the first one (topmost in DOM)
            return errorInputs[0];
        },

        /**
         * Scroll to the specified element
         *
         * @param {HTMLElement} element - Element to scroll to
         */
        scrollToElement(element) {
            if (!element) {
                return;
            }

            // Get the element's position
            const elementRect = element.getBoundingClientRect();
            const absoluteElementTop = elementRect.top + window.pageYOffset;
            const middle =
                absoluteElementTop -
                window.innerHeight / 2 +
                elementRect.height / 2;

            // Scroll to calculated position with offset
            window.scrollTo({
                top: Math.max(0, middle - this.offset),
                behavior: this.behavior,
            });

            // Optional: Focus the error input for keyboard accessibility
            if (
                element.tagName === 'INPUT' ||
                element.tagName === 'SELECT' ||
                element.tagName === 'TEXTAREA'
            ) {
                element.focus({ preventScroll: true });
            }
        },

        destroy() {
            // Clean up Livewire event listener
            if (this.commitListener && this.$wire) {
                this.$wire.$off('commit', this.commitListener);
            }
        },
    };
}

// Self-register when Alpine initializes
document.addEventListener('alpine:init', () => {
    window.Alpine.data('validationErrorScroller', validationErrorScroller);
});

/**
 * Global auto-initialization
 * Automatically scrolls to validation errors on all Livewire updates
 */
document.addEventListener('livewire:init', () => {
    let hasScrolled = false;

    // Listen to all Livewire commit events globally
    window.Livewire.hook('commit', ({ component, commit, respond }) => {
        // Reset scroll flag for each commit
        hasScrolled = false;

        // After commit is processed, check for errors
        respond(() => {
            // Use setTimeout to ensure DOM has updated
            setTimeout(() => {
                if (hasScrolled) {
                    return;
                }

                // Find first error element
                const errorInputs = document.querySelectorAll(
                    '.input-error, .select-error, .textarea-error',
                );
                if (errorInputs.length === 0) {
                    return;
                }

                const errorElement = errorInputs[0];
                if (!errorElement) {
                    return;
                }

                // Scroll to error
                const elementRect = errorElement.getBoundingClientRect();
                const absoluteElementTop = elementRect.top + window.pageYOffset;
                const middle =
                    absoluteElementTop -
                    window.innerHeight / 2 +
                    elementRect.height / 2;

                window.scrollTo({
                    top: Math.max(0, middle - 100), // Default offset
                    behavior: 'smooth',
                });

                // Focus for accessibility
                if (
                    errorElement.tagName === 'INPUT' ||
                    errorElement.tagName === 'SELECT' ||
                    errorElement.tagName === 'TEXTAREA'
                ) {
                    errorElement.focus({ preventScroll: true });
                }

                hasScrolled = true;
            }, 50); // Small delay to ensure DOM updates
        });
    });
});
