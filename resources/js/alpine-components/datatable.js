/**
 * Alpine.js DataTable Component
 *
 * Provides frontend state management for DataTable components.
 * Note: Modal handling is now done by the global action-modal Livewire component.
 */
export function dataTable(id = null) {
    return {
        // ===== Local Alpine State =====
        id: id,
        openFilters: false,
        pendingAction: null,

        /**
         * Initialize component and register listeners
         */
        init() {
            // Store listener references for cleanup
            this._confirmListener = (e) => this.confirmAction(e.detail);
            this._scrollListener = () =>
                this.$el.scrollIntoView({ behavior: 'smooth' });
            this._cleanUrlListener = () =>
                window.history.replaceState(
                    {},
                    document.title,
                    window.location.pathname,
                );

            // Register listeners
            window.addEventListener(
                `datatable:action-confirmed:${this.id}`,
                this._confirmListener,
            );
            window.addEventListener(
                `datatable:scroll-to-top:${this.id}`,
                this._scrollListener,
            );
            window.addEventListener(
                `datatable:clean-url:${this.id}`,
                this._cleanUrlListener,
            );
        },

        /**
         * Cleanup listeners on destroy
         */
        destroy() {
            window.removeEventListener(
                `datatable:action-confirmed:${this.id}`,
                this._confirmListener,
            );
            window.removeEventListener(
                `datatable:scroll-to-top:${this.id}`,
                this._scrollListener,
            );
            window.removeEventListener(
                `datatable:clean-url:${this.id}`,
                this._cleanUrlListener,
            );
        },

        // ===== Filter Methods =====
        toggleFilters() {
            this.openFilters = !this.openFilters;
        },

        closeFilters() {
            this.openFilters = false;
        },

        // ===== Action Methods =====

        /**
         * Execute action with confirmation if needed
         */
        executeActionWithConfirmation(actionKey, uuid = null, isBulk = false) {
            const wire =
                this.$wire || this.$el.closest('[wire\\:id]')?.__livewire;
            if (!wire) {
                return;
            }

            const method = isBulk
                ? 'getBulkActionConfirmation'
                : 'getActionConfirmation';

            wire[method](actionKey, uuid)
                .then((config) => {
                    if (config?.required) {
                        this.pendingAction = { actionKey, uuid, isBulk };

                        const eventPayload = {
                            title: config.title || 'Confirm Action',
                            message:
                                config.message ||
                                config.content ||
                                'Are you sure you want to proceed?',
                            confirmLabel: config.confirmText || 'Confirm',
                            cancelLabel: config.cancelText || 'Cancel',
                            confirmEvent: `datatable:action-confirmed:${this.id}`,
                            confirmData: { actionKey, uuid, isBulk },
                        };

                        window.dispatchEvent(
                            new CustomEvent('confirm-modal', {
                                detail: eventPayload,
                                bubbles: true,
                            }),
                        );
                    } else {
                        this.confirmAction({ actionKey, uuid, isBulk });
                    }
                })
                .catch(() => {
                    // Silently handle error
                });
        },

        /**
         * Confirm and execute action
         * Triggered via global window event listener in Blade
         */
        confirmAction(data) {
            const actionData = data || this.pendingAction;
            const wire =
                this.$wire || this.$el.closest('[wire\\:id]')?.__livewire;

            if (!actionData || !wire) {
                return;
            }

            const { actionKey, uuid, isBulk } = actionData;

            if (isBulk) {
                wire.executeBulkAction(actionKey);
            } else {
                wire.executeAction(actionKey, uuid);
            }

            this.pendingAction = null;
        },

        /**
         * Clear pending action if cancelled
         */
        cancelAction() {
            this.pendingAction = null;
        },
    };
}

if (typeof window.Alpine !== 'undefined') {
    window.Alpine.data('dataTable', dataTable);
}
