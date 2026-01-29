/**
 * Merge Tag Picker Component
 *
 * Allows users to search and insert merge tags into content fields.
 * Supports inserting tags at cursor position in textareas.
 */
export default (availableTags, targetRef) => ({
    availableTags: {},
    targetRef: null,
    isOpen: false,
    search: '',
    view: 'entities', // 'entities' or 'tags'
    selectedEntity: null,

    init() {
        // CSP Fix: Parse stringified JSON inputs if necessary
        // This allows passing data via @js(json_encode(...)) to avoid JSON.parse in HTML attributes
        try {
            this.availableTags =
                typeof availableTags === 'string'
                    ? JSON.parse(availableTags)
                    : availableTags || {};

            this.targetRef =
                typeof targetRef === 'string' && targetRef.startsWith('"')
                    ? JSON.parse(targetRef)
                    : targetRef;
        } catch (e) {
            console.error('MergeTagPicker: Failed to parse inputs', e);
            this.availableTags = {};
        }

        // Listen for external open events (e.g. from GrapesJS RTE)
        this.openModalHandler = () => {
            this.openModal();
        };
        window.addEventListener('open-merge-tag-modal', this.openModalHandler);
    },

    destroy() {
        if (this.openModalHandler) {
            window.removeEventListener(
                'open-merge-tag-modal',
                this.openModalHandler,
            );
        }
    },

    openModal() {
        this.isOpen = true;
        this.resetView();
    },

    closeModal() {
        this.isOpen = false;
        this.resetView();
    },

    resetView() {
        this.view = 'entities';
        this.selectedEntity = null;
        this.search = '';
    },

    selectEntity(key) {
        this.selectedEntity = key;
        this.view = 'tags';
        this.search = '';
    },

    goBack() {
        this.view = 'entities';
        this.selectedEntity = null;
        this.search = '';
    },

    get filteredTags() {
        // If in entities view, we don't return tags
        if (this.view === 'entities' || !this.selectedEntity) {
            return {};
        }

        const group = this.availableTags[this.selectedEntity];
        if (!group) return {};

        // If no search, return the full group
        if (!this.search.trim()) {
            return {
                [this.selectedEntity]: group,
            };
        }

        const searchLower = this.search.toLowerCase();
        const matchingTags = {};

        for (const [tagKey, tag] of Object.entries(group.tags)) {
            if (
                tagKey.toLowerCase().includes(searchLower) ||
                tag.label.toLowerCase().includes(searchLower)
            ) {
                matchingTags[tagKey] = tag;
            }
        }

        if (Object.keys(matchingTags).length > 0) {
            return {
                [this.selectedEntity]: {
                    label: group.label,
                    color: group.color, // Preserve color
                    tags: matchingTags,
                },
            };
        }

        return {};
    },

    get isEmpty() {
        return Object.keys(this.filteredTags).length === 0;
    },

    insertTag(tagKey) {
        const tagText = `{{ ${tagKey} }}`;

        this.closeModal();

        // Delay insertion and focus to ensure modal loop doesn't steal focus back
        setTimeout(() => {
            if (this.targetRef) {
                // Try explicit x-ref first, then name, then ID
                let target = this.$refs[this.targetRef];

                if (!target) {
                    target = document.querySelector(
                        `[name="${this.targetRef}"], #${this.targetRef}`,
                    );
                }

                if (
                    target &&
                    (target.tagName === 'TEXTAREA' ||
                        target.tagName === 'INPUT')
                ) {
                    this.insertAtCursor(target, tagText);
                    this.syncWithLivewire(target);
                } else if (target) {
                    // Dispatch custom event for complex editors (like GrapeJS)
                    target.dispatchEvent(
                        new CustomEvent('insert-text', {
                            detail: { text: tagText },
                            bubbles: true,
                        }),
                    );
                } else {
                    this.copyToClipboard(tagText);
                }
            } else {
                this.copyToClipboard(tagText);
            }
        }, 100);
    },

    insertAtCursor(element, text) {
        const start = element.selectionStart;
        const end = element.selectionEnd;
        const value = element.value;

        element.value = value.substring(0, start) + text + value.substring(end);
        element.selectionStart = element.selectionEnd = start + text.length;
        element.focus();

        // Dispatch input event for reactivity
        element.dispatchEvent(new Event('input', { bubbles: true }));
    },

    syncWithLivewire(element) {
        // Trigger Livewire sync if wire:model is present
        const wireModel =
            element.getAttribute('wire:model') ||
            element.getAttribute('wire:model.live') ||
            element.getAttribute('wire:model.blur');

        if (wireModel && window.Livewire) {
            const component = element.closest('[wire\\:id]');
            if (component) {
                const id = component.getAttribute('wire:id');
                const livewireComponent = window.Livewire.find(id);
                if (livewireComponent) {
                    livewireComponent.set(wireModel, element.value);
                }
            }
        }
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            window.dispatchEvent(
                new CustomEvent('notification', {
                    detail: {
                        title:
                            window.translations?.copied ||
                            'Copied to clipboard',
                        type: 'success',
                    },
                }),
            );
        });
    },
});
