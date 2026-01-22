/**
 * Merge Tag Picker Component
 *
 * Allows users to search and insert merge tags into content fields.
 * Supports inserting tags at cursor position in textareas.
 */
export default (availableTags, targetRef) => ({
    availableTags: availableTags || {},
    targetRef: targetRef,
    isOpen: false,
    search: '',

    get filteredTags() {
        if (!this.search.trim()) {
            return this.availableTags;
        }

        const searchLower = this.search.toLowerCase();
        const filtered = {};

        for (const [groupKey, group] of Object.entries(this.availableTags)) {
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
                filtered[groupKey] = {
                    label: group.label,
                    tags: matchingTags,
                };
            }
        }

        return filtered;
    },

    insertTag(tagKey) {
        const tagText = `{{ ${tagKey} }}`;

        if (this.targetRef) {
            const target = document.querySelector(
                `[x-ref="${this.targetRef}"], [name="${this.targetRef}"], #${this.targetRef}`,
            );

            if (
                target &&
                (target.tagName === 'TEXTAREA' || target.tagName === 'INPUT')
            ) {
                this.insertAtCursor(target, tagText);
                this.syncWithLivewire(target);
            } else {
                this.copyToClipboard(tagText);
            }
        } else {
            this.copyToClipboard(tagText);
        }

        this.isOpen = false;
        this.search = '';
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
