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
