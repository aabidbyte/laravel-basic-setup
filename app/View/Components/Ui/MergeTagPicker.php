<?php

namespace App\View\Components\Ui;

use App\Services\EmailTemplate\ColumnTagDiscovery;
use App\Services\EmailTemplate\EntityTypeRegistry;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class MergeTagPicker extends Component
{
    public array $availableTags = [];

    public function __construct(
        public array $entityTypes,
        public array $contextVariables,
        public mixed $target = null,
        protected EntityTypeRegistry $registry, // injected
        protected ColumnTagDiscovery $discovery, // injected
    ) {
        $this->availableTags = $this->buildAvailableTags();
    }

    public function render()
    {
        return view('components.ui.merge-tag-picker');
    }

    protected function buildAvailableTags(): array
    {
        $tagsData = [];

        $this->addEntityTags($tagsData);
        $this->addContextTags($tagsData);

        return $tagsData;
    }

    protected function addEntityTags(array &$tagsData): void
    {
        foreach ($this->entityTypes as $entityType) {
            $foundTags = $this->discovery->getTagsForEntityType($entityType);

            if (! empty($foundTags)) {
                $tagsData[$entityType] = [
                    'label' => __("types.$entityType"),
                    'color' => $this->registry->getColor($entityType),
                    'tags' => $foundTags,
                    'count' => count($foundTags),
                ];
            }
        }
    }

    protected function addContextTags(array &$tagsData): void
    {
        if (empty($this->contextVariables)) {
            return;
        }

        $contextTags = [];
        foreach ($this->contextVariables as $variable) {
            $contextTags["context.{$variable}"] = [
                'label' => Str::of($variable)->replace('_', ' ')->title()->toString(),
                'type' => 'string',
                'example' => 'Value',
            ];
        }

        $tagsData['context'] = [
            'label' => __('email_templates.merge_tags.context'),
            'color' => $this->registry->getColor('context'),
            'tags' => $contextTags,
            'count' => count($contextTags),
        ];
    }
}
