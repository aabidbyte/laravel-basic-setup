@props([
    'entityTypes' => [],
    'contextVariables' => [],
    'target' => null, // Alpine x-ref or input name to insert into
])

@php
    use App\Services\EmailTemplate\ColumnTagDiscovery;
    use App\Services\EmailTemplate\EntityTypeRegistry;

    $registry = app(EntityTypeRegistry::class);
    $discovery = app(ColumnTagDiscovery::class);

    // Discover tags for each entity type
    $availableTags = [];
    foreach ($entityTypes as $entityType) {
        $tags = $discovery->getTagsForEntityType($entityType);
        if (!empty($tags)) {
            $availableTags[$entityType] = [
                'label' => __("types.$entityType"),
                'tags' => $tags,
            ];
        }
    }

    // Add context variables as a group
    if (!empty($contextVariables)) {
        $contextTags = [];
        foreach ($contextVariables as $variable) {
            $contextTags["context.{$variable}"] = [
                'label' => \Illuminate\Support\Str::of($variable)->replace('_', ' ')->title()->toString(),
                'type' => 'string',
                'example' => 'Value',
            ];
        }
        $availableTags['context'] = [
            'label' => __('email_templates.merge_tags.context'),
            'tags' => $contextTags,
        ];
    }
@endphp

<div x-data="mergeTagPicker(@js($availableTags), @js($target))"
     {{ $attributes->merge(['class' => 'relative']) }}>
    {{-- Toggle Button --}}
    <x-ui.button type="button"
                 variant="ghost"
                 size="sm"
                 @click="isOpen = !isOpen"
                 class="gap-2">
        <x-ui.icon name="code-bracket"
                   size="sm"></x-ui.icon>
        {{ __('email_templates.merge_tags.insert') }}
    </x-ui.button>

    {{-- Dropdown Panel --}}
    <div x-show="isOpen"
         x-cloak
         @click.away="isOpen = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-base-100 border-base-300 absolute z-50 mt-2 w-80 rounded-lg border p-4 shadow-lg">

        {{-- Search --}}
        <div class="mb-3">
            <x-ui.input type="text"
                        x-model="search"
                        :placeholder="__('email_templates.merge_tags.search')"
                        size="sm"></x-ui.input>
        </div>

        {{-- Tag Groups --}}
        <div class="max-h-64 space-y-4 overflow-y-auto">
            <template x-for="(group, groupKey) in filteredTags"
                      :key="groupKey">
                <div>
                    <h4 class="text-base-content/70 mb-2 text-sm font-semibold"
                        x-text="group.label"></h4>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="(tag, tagKey) in group.tags"
                                  :key="tagKey">
                            <button type="button"
                                    class="badge badge-ghost hover:badge-primary cursor-pointer text-xs transition-colors"
                                    @click="insertTag(tagKey)"
                                    :title="tag.example">
                                <span x-text="'{{ ' + tagKey + ' }}'"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="Object.keys(filteredTags).length === 0"
                 class="text-base-content/60 py-4 text-center text-sm">
                {{ __('email_templates.merge_tags.no_tags') }}
            </div>
        </div>

        {{-- Help Text --}}
        <div class="border-base-300 mt-3 border-t pt-3">
            <p class="text-base-content/60 text-xs">
                {{ __('email_templates.merge_tags.help') }}
            </p>
        </div>
    </div>
</div>
