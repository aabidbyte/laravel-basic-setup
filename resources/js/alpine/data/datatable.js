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
                            viewType: 'confirm',
                            viewProps: {
                                title: config.title || 'Confirm Action',
                                content:
                                    config.message ||
                                    config.content ||
                                    'Are you sure you want to proceed?',
                                confirmLabel: config.confirmText || 'Confirm',
                                cancelLabel: config.cancelText || 'Cancel',
                                actionKey,
                                uuid,
                                isBulk,
                            },
                            viewTitle: config.title || 'Confirm Action',
                            datatableId: this.id,
                        };

                        window.dispatchEvent(
                            new CustomEvent('open-datatable-modal', {
                                detail: eventPayload,
                                bubbles: true,
                            }),
                        );
                    } else {
                        // Backend confirmation not required, execute directly
                        if (isBulk) {
                            wire.executeBulkAction(actionKey);
                        } else {
                            wire.executeAction(actionKey, uuid);
                        }
                    }
                })
                .catch(() => {
                    // Silently handle error
                });
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
