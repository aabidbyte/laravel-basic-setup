/**
 * Datatable Action Modal Alpine Component
 * Self-registers as 'actionModal'
 */
export function actionModal() {
    return {
        modalIsOpen: false,
        isLoading: false,
        _loadingListener: null,
        _morphHookRemove: null,
        _wireId: null,

        init() {
            // Store wire ID on init for later comparison
            this._wireId = this.$wire?.id;

            // Entangle with Livewire
            this.$watch('$wire.isOpen', (value) => {
                this.modalIsOpen = value;
            });
            this.modalIsOpen = this.$wire?.isOpen ?? false;

            // Store listener reference for cleanup
            this._loadingListener = () => {
                this.isLoading = true;
                this.modalIsOpen = true;
            };

            // Listen for loading trigger
            window.addEventListener(
                'datatable-modal-loading',
                this._loadingListener,
            );

            // Use Livewire hook to detect when component has finished updating
            // Store the cleanup function returned by hook()
            this._morphHookRemove = window.Livewire.hook(
                'morph.updated',
                ({ component }) => {
                    // Check if wire instance still exists (not destroyed by navigation)
                    if (!this.$wire || !this.$wire.__instance) {
                        return;
                    }
                    if (
                        component.id === this._wireId &&
                        this.modalIsOpen &&
                        this.$wire.modalView
                    ) {
                        this.isLoading = false;
                    }
                },
            );
        },

        destroy() {
            // Clean up event listener on component destroy
            if (this._loadingListener) {
                window.removeEventListener(
                    'datatable-modal-loading',
                    this._loadingListener,
                );
            }
            // Clean up Livewire hook
            if (
                this._morphHookRemove &&
                typeof this._morphHookRemove === 'function'
            ) {
                this._morphHookRemove();
            }
        },
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('actionModal', actionModal);
});
