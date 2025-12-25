/**
 * Alpine.js DataTable Component
 *
 * Provides frontend state management for DataTable components:
 * - Filter panel visibility
 * - Modal state
 * - Row hover state
 *
 * IMPORTANT: Following project conventions from docs/alpinejs/livewire-integration.md:
 * - $wire is reactive and automatically available in Alpine context (do NOT pass as parameter)
 * - Validate $wire before calling methods to ensure component exists
 *
 * Usage:
 * <div x-data="dataTable()">
 *   ...
 * </div>
 */
export function dataTable() {
    return {
        // ===== Local Alpine State (UI only) =====
        openFilters: false,
        activeModal: null,
        hoveredRow: null,
        pendingAction: null,
        confirmationConfig: null,

        // ===== Filter Methods =====

        /**
         * Toggle filter panel visibility
         */
        toggleFilters() {
            this.openFilters = !this.openFilters;
        },

        /**
         * Close filter panel
         */
        closeFilters() {
            this.openFilters = false;
        },

        // ===== Modal Methods =====

        // ===== Modal Methods =====

        /**
         * Open confirmation modal
         */
        openModal() {
            this.activeModal = "confirm-action-modal";
            if (this.$refs.confirmModal) {
                this.$refs.confirmModal.showModal();
            }
        },

        /**
         * Close the active modal
         */
        closeModal() {
            this.activeModal = null;
            if (this.$refs.confirmModal) {
                this.$refs.confirmModal.close();
            }
        },

        /**
         * Execute action with confirmation if needed
         *
         * @param {string} actionKey - Action key
         * @param {string} uuid - Row UUID (optional for bulk actions)
         * @param {boolean} isBulk - Whether this is a bulk action
         */
        executeActionWithConfirmation(actionKey, uuid = null, isBulk = false) {
            if (typeof $wire === "undefined" || !$wire) {
                return;
            }

            this.pendingAction = { actionKey, uuid, isBulk };

            const method = isBulk
                ? "getBulkActionConfirmation"
                : "getActionConfirmation";

            if (typeof $wire[method] !== "function") {
                this.confirmAction();
                return;
            }

            $wire[method](actionKey, uuid).then((config) => {
                if (config?.required) {
                    this.confirmationConfig = config;
                    this.openModal();
                } else {
                    this.confirmAction();
                }
            });
        },

        /**
         * Confirm and execute pending action
         */
        confirmAction() {
            if (!this.pendingAction || typeof $wire === "undefined" || !$wire) {
                return;
            }

            const { actionKey, uuid, isBulk } = this.pendingAction;

            if (isBulk) {
                $wire.executeBulkAction(actionKey);
            } else {
                $wire.executeAction(actionKey, uuid);
            }

            this.pendingAction = null;
            this.confirmationConfig = null;
            this.closeModal();
        },

        /**
         * Cancel pending action
         */
        cancelAction() {
            this.pendingAction = null;
            this.confirmationConfig = null;
            this.closeModal();
        },

        /**
         * Set hovered row
         *
         * @param {string|null} uuid - Row UUID or null
         */
        setHoveredRow(uuid) {
            this.hoveredRow = uuid;
        },
    };
}

// Register globally if Alpine is available
if (typeof window.Alpine !== "undefined") {
    window.Alpine.data("dataTable", dataTable);
}
