document.addEventListener('alpine:init', () => {
    Alpine.data('validationErrorScroller', (config = {}) => ({
        offset: config.offset || 100,
        behavior: config.behavior || 'smooth',
        block: config.block || 'center',
        hasScrolled: false,
        commitListener: null,

        init() {
            this.commitListener = () => {
                this.scrollToFirstError();
            };
            this.$wire.$on('commit', this.commitListener);
            this.$nextTick(() => {
                this.scrollToFirstError();
            });
        },

        scrollToFirstError() {
            this.hasScrolled = false;
            this.$nextTick(() => {
                const errorElement = this.findFirstError();
                if (errorElement && !this.hasScrolled) {
                    this.scrollToElement(errorElement);
                    this.hasScrolled = true;
                }
            });
        },

        findFirstError() {
            const errorInputs = document.querySelectorAll(
                '.input-error, .select-error, .textarea-error',
            );
            return errorInputs.length > 0 ? errorInputs[0] : null;
        },

        scrollToElement(element) {
            if (!element) return;
            const elementRect = element.getBoundingClientRect();
            const absoluteElementTop = elementRect.top + window.pageYOffset;
            const middle =
                absoluteElementTop -
                window.innerHeight / 2 +
                elementRect.height / 2;

            window.scrollTo({
                top: Math.max(0, middle - this.offset),
                behavior: this.behavior,
            });

            if (['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName)) {
                element.focus({ preventScroll: true });
            }
        },

        destroy() {
            if (this.commitListener && this.$wire) {
                this.$wire.$off('commit', this.commitListener);
            }
        },
    }));
});

// Global Hook
document.addEventListener('livewire:init', () => {
    let hasScrolled = false;
    window.Livewire.hook('commit', ({ component, commit, respond }) => {
        hasScrolled = false;
        respond(() => {
            setTimeout(() => {
                if (hasScrolled) return;
                const errorInputs = document.querySelectorAll(
                    '.input-error, .select-error, .textarea-error',
                );
                if (errorInputs.length === 0) return;
                const errorElement = errorInputs[0];
                if (!errorElement) return;

                const elementRect = errorElement.getBoundingClientRect();
                const absoluteElementTop = elementRect.top + window.pageYOffset;
                const middle =
                    absoluteElementTop -
                    window.innerHeight / 2 +
                    elementRect.height / 2;
                window.scrollTo({
                    top: Math.max(0, middle - 100),
                    behavior: 'smooth',
                });

                if (
                    ['INPUT', 'SELECT', 'TEXTAREA'].includes(
                        errorElement.tagName,
                    )
                ) {
                    errorElement.focus({ preventScroll: true });
                }
                hasScrolled = true;
            }, 50);
        });
    });
});
