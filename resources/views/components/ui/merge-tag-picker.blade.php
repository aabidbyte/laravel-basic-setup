{{--
    Component: Merge Tag Picker
    
    Provides a modal-based search and selection interface for merge tags.
    Integrates with Alpine.js registered component 'mergeTagPicker'.
    
    Props:
    - target: The ID of the input/textarea where tags should be inserted.
    - entityTypes: Array of entity types to discover tags for.
    - contextVariables: Array of context variables to include as tags.
--}}
@props([
    'target' => null,
    'entityTypes' => [],
    'contextVariables' => [],
])

<div x-data='mergeTagPicker({!! json_encode($availableTags, JSON_HEX_APOS) !!}, {!! json_encode($target, JSON_HEX_APOS) !!})'
     {{ $attributes->merge(['class' => 'inline-block']) }}>

    {{-- Trigger Button --}}
    <x-ui.button type="button"
                 variant="ghost"
                 size="sm"
                 @click="openModal()"
                 class="gap-2">
        <x-ui.icon name="code-bracket"
                   size="sm"></x-ui.icon>
        {{ __('email_templates.merge_tags.insert') }}
    </x-ui.button>

    {{-- Modal Component --}}
    <x-ui.base-modal id="mergeTagPickerModal_{{ Str::random(8) }}"
                     open-state="isOpen"
                     :use-parent-state="true"
                     :title="__('email_templates.merge_tags.insert')"
                     :close-action="'closeModal()'"
                     max-width="2xl">

        {{-- View 1: Entity Types --}}
        <div x-show="view === 'entities'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0">
            <div class="grid grid-cols-2 gap-3 p-1">
                <template x-for="(group, key) in availableTags"
                          :key="key">
                    <x-ui.button type="button"
                                 variant="outline"
                                 class="flex h-auto flex-col items-center gap-2 py-4"
                                 x-bind:class="{
                                     'btn-primary': group.color === 'primary',
                                     'btn-secondary': group.color === 'secondary',
                                     'btn-accent': group.color === 'accent',
                                     'btn-neutral': !['primary', 'secondary', 'accent'].includes(group.color)
                                 }"
                                 @click="selectEntity(key)">
                        <div class="h-2 w-2 rounded-full"
                             :class="'bg-' + group.color"></div>
                        <span class="text-sm font-semibold"
                              x-text="group.label"></span>
                        <span class="text-xs opacity-70"
                              x-text="'(' + group.count + ')'"></span>
                    </x-ui.button>
                </template>
            </div>
        </div>

        {{-- View 2: Tags --}}
        <div x-show="view === 'tags'"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0">

            {{-- Navigation --}}
            <div class="mb-4 flex items-center gap-3">
                <x-ui.button type="button"
                             @click="goBack()"
                             variant="ghost"
                             size="sm"
                             square>
                    <x-ui.icon name="arrow-left"
                               size="sm"></x-ui.icon>
                </x-ui.button>
                <div class="flex-1">
                    {{-- Search Input (Only in Tags View) --}}
                    <x-ui.search x-model="search"
                                 :placeholder="__('email_templates.merge_tags.search')"
                                 class="w-full"></x-ui.search>
                </div>
            </div>

            {{-- Tag Groups (Filtered by selected entity) --}}
            <div class="max-h-[50vh] space-y-6 overflow-y-auto px-1">
                <template x-for="(group, groupKey) in filteredTags"
                          :key="groupKey">
                    <div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(tag, tagKey) in group.tags"
                                      :key="tagKey">
                                <button type="button"
                                        @click="insertTag(tagKey)"
                                        class="group transition-transform active:scale-95"
                                        :title="tag.example">
                                    <x-ui.badge :text="'tag.label'"
                                                x-text="tag.label"
                                                x-bind:class="'badge-' + group.color"
                                                variant="soft"
                                                class="cursor-pointer py-3 text-xs transition-all group-hover:brightness-95"></x-ui.badge>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Empty State --}}
                <div x-show="isEmpty"
                     class="py-12 text-center opacity-60">
                    <x-ui.icon name="magnifying-glass"
                               class="mx-auto mb-2 h-8 w-8 opacity-50"></x-ui.icon>
                    <p class="text-sm font-medium">{{ __('email_templates.merge_tags.no_tags') }}</p>
                </div>
            </div>
        </div>

        {{-- Footer Help Text --}}
        <div class="border-base-200 mt-6 border-t pt-4">
            <p class="text-xs opacity-60">
                {{ __('email_templates.merge_tags.help') }}
            </p>
        </div>
    </x-ui.base-modal>
</div>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('mergeTagPicker', (availableTags, targetRef) => ({
                    availableTags: {},
                    targetRef: null,
                    isOpen: false,
                    search: '',
                    view: 'entities', // 'entities' or 'tags'
                    selectedEntity: null,

                    init() {
                        // CSP Fix: Parse stringified JSON inputs if necessary
                        try {
                            this.availableTags =
                                typeof availableTags === 'string' ?
                                JSON.parse(availableTags) :
                                availableTags || {};

                            this.targetRef =
                                typeof targetRef === 'string' && targetRef.startsWith('"') ?
                                JSON.parse(targetRef) :
                                targetRef;
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
                        const tagText = `@{{ $ {
    tagKey
} }}`;

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
                                            detail: {
                                                text: tagText
                                            },
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
                        element.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
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
                                        title: window.translations?.copied ||
                                            'Copied to clipboard',
                                        type: 'success',
                                    },
                                }),
                            );
                        });
                    },
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
