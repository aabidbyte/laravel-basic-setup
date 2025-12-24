/**
 * Alpine.js DataTable Component
 *
 * Provides frontend state management for DataTable components:
 * - Row selection (single and bulk)
 * - Filter panel visibility
 * - Modal state
 * - Row hover state
 *
 * IMPORTANT: Following project conventions from docs/alpinejs/livewire-integration.md:
 * - $wire is reactive and automatically available in Alpine context (do NOT pass as parameter)
 * - Use $wire.$entangle() for bidirectional sync with Livewire
 * - Validate $wire before calling methods to ensure component exists
 *
 * Usage:
 * <div x-data="dataTable(@js(['pageUuids' => $rows->pluck('uuid')->toArray()]))">
 *   ...
 * </div>
 */
export function dataTable(initialData = {}) {
    return {
        // ===== Entangled State (synced with Livewire) =====
        // NOTE: $wire is reactive and automatically available in Alpine context
        // We initialize selected in init() using $wire.$entangle()
        selected: [],

        // ===== Local Alpine State (UI only) =====
        selectPage: false,
        openFilters: false,
        activeModal: null,
        hoveredRow: null,
        pageUuids: initialData.pageUuids || [],
        pendingAction: null, // Stores action waiting for confirmation
        confirmationConfig: null, // Stores confirmation modal config

        // ===== Initialization =====
        init() {
            // Initialize entangled state with Livewire
            // $wire is reactive and automatically available
            if (
                typeof $wire !== "undefined" &&
                $wire &&
                typeof $wire.$entangle === "function"
            ) {
                this.selected = $wire.$entangle("selected");
            }

            // Watch for changes to selected array to update selectPage state
            this.$watch("selected", (value) => {
                // Update selectPage based on current page UUIDs
                this.selectPage =
                    this.pageUuids.length > 0 &&
                    this.pageUuids.every((uuid) => value.includes(uuid));
            });

            // Watch for changes to pageUuids (when data changes via search/filter/pagination)
            this.$watch("pageUuids", (newUuids, oldUuids) => {
                // When page UUIDs change, update selectPage checkbox state
                this.selectPage =
                    newUuids.length > 0 &&
                    newUuids.every((uuid) => this.selected.includes(uuid));
            });

            // Listen for datatable-updated event from Livewire
            this.$el.addEventListener("datatable-updated", (event) => {
                if (event.detail && event.detail.pageUuids) {
                    this.pageUuids = event.detail.pageUuids;
                }
            });
        },

        // ===== Selection Methods =====

        /**
         * Update page UUIDs from current rows
         */
        updatePageUuids() {
            // This will be called after Livewire updates
            // The pageUuids will be updated via wire:key on the parent div
        },

        /**
         * Toggle selection of all rows on current page
         */
        toggleSelectPage() {
            this.selectPage = !this.selectPage;

            if (this.selectPage) {
                // Select all UUIDs on current page
                this.selected = [
                    ...new Set([...this.selected, ...this.pageUuids]),
                ];
            } else {
                // Deselect all UUIDs on current page
                this.selected = this.selected.filter(
                    (uuid) => !this.pageUuids.includes(uuid)
                );
            }
        },

        /**
         * Check if a row is selected
         *
         * @param {string} uuid - Row UUID
         * @returns {boolean}
         */
        isSelected(uuid) {
            return this.selected.includes(uuid);
        },

        /**
         * Toggle selection of a single row
         *
         * @param {string} uuid - Row UUID
         */
        toggleRow(uuid) {
            if (this.isSelected(uuid)) {
                this.selected = this.selected.filter((id) => id !== uuid);
            } else {
                this.selected.push(uuid);
            }
        },

        /**
         * Clear all selections
         */
        clearSelection() {
            this.selected = [];
            this.selectPage = false;
        },

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

        /**
         * Open a modal
         *
         * @param {string} modalName - Modal identifier
         */
        openModal(modalName) {
            this.activeModal = modalName;
            if (
                modalName === "confirm-action-modal" &&
                this.$refs.confirmModal
            ) {
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
         * Check if a specific modal is open
         *
         * @param {string} modalName - Modal identifier
         * @returns {boolean}
         */
        isModalOpen(modalName) {
            return this.activeModal === modalName;
        },

        // ===== Row Methods =====

        /**
         * Handle row click
         *
         * @param {string} uuid - Row UUID
         */
        handleRowClick(uuid) {
            // Validate $wire before calling (follows project conventions)
            // $wire is reactive - automatically updated by Livewire
            if (
                typeof $wire === "undefined" ||
                !$wire ||
                typeof $wire.rowClicked !== "function"
            ) {
                return;
            }

            // Additional validation: check if component still exists in Livewire registry
            if (window.Livewire && $wire.__instance?.id) {
                const component = window.Livewire.find($wire.__instance.id);
                if (!component) {
                    // Component was removed from DOM but $wire still exists
                    return;
                }
            }

            // Safe to call - component exists and is valid
            $wire.rowClicked(uuid);
        },

        /**
         * Execute action with confirmation if needed
         *
         * @param {string} actionKey - Action key
         * @param {string} uuid - Row UUID (optional for bulk actions)
         * @param {boolean} isBulk - Whether this is a bulk action
         */
        executeActionWithConfirmation(actionKey, uuid = null, isBulk = false) {
            this.pendingAction = { actionKey, uuid, isBulk };

            // Check if action requires confirmation
            if (typeof $wire !== "undefined" && $wire) {
                const method = isBulk
                    ? "getBulkActionConfirmation"
                    : "getActionConfirmation";
                if (typeof $wire[method] === "function") {
                    $wire[method](actionKey, uuid).then((config) => {
                        if (config && config.required) {
                            this.confirmationConfig = config;
                            this.openModal("confirm-action-modal");
                        } else {
                            this.confirmAction();
                        }
                    });
                } else {
                    // No confirmation needed, execute directly
                    this.confirmAction();
                }
            }
        },

        /**
         * Confirm and execute pending action
         */
        confirmAction() {
            if (!this.pendingAction) {
                return;
            }

            const { actionKey, uuid, isBulk } = this.pendingAction;

            if (typeof $wire !== "undefined" && $wire) {
                if (isBulk) {
                    $wire.executeBulkAction(actionKey);
                } else {
                    $wire.executeAction(actionKey, uuid);
                }
            }

            // Clean up
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

        /**
         * Check if a row is hovered
         *
         * @param {string} uuid - Row UUID
         * @returns {boolean}
         */
        isRowHovered(uuid) {
            return this.hoveredRow === uuid;
        },

        // ===== Computed Properties =====

        /**
         * Get count of selected rows
         *
         * @returns {number}
         */
        get selectedCount() {
            return this.selected.length;
        },

        /**
         * Check if any rows are selected
         *
         * @returns {boolean}
         */
        get hasSelection() {
            return this.selected.length > 0;
        },
    };
}

// Register globally if Alpine is available
if (typeof window.Alpine !== "undefined") {
    window.Alpine.data("dataTable", dataTable);
}
