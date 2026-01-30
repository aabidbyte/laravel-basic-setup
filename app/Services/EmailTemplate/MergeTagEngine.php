<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

use Illuminate\Database\Eloquent\Model;

/**
 * Merge Tag Engine.
 *
 * Processes email templates and replaces merge tags with actual values.
 *
 * Tag syntax:
 * - {{ entity.column }}  - Escaped output (default, XSS-safe)
 * - {{{ entity.column }}} - Raw output (for URLs, HTML snippets)
 */
class MergeTagEngine
{
    private const RAW_TAG_PATTERN = '/\{\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)\s*\}\}\}/u';

    private const ESCAPED_TAG_PATTERN = '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)\s*\}\}/u';

    private const EXTRACT_TAG_PATTERN = '/\{?\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)\s*\}\}\}?/u';

    /** @var array<string, string> */
    protected array $globalTags = [];

    /** @var array<string, string> */
    protected array $contextVariables = [];

    /** @var array<string, Model> */
    protected array $entities = [];

    public function __construct(
        protected ColumnTagDiscovery $columnDiscovery,
        protected EntityTypeRegistry $entityRegistry,
    ) {
        $this->initializeGlobalTags();
    }

    /**
     * Initialize global tags with app-level values.
     */
    protected function initializeGlobalTags(): void
    {
        $this->globalTags = [
            'app.name' => config('app.name', 'Application'),
            'app.url' => config('app.url', ''),
            'app.logo_url' => config('app.logo_url', ''),
            'sender.name' => config('mail.from.name', 'Support'),
            'sender.email' => config('mail.from.address', ''),
            'meta.year' => (string) now()->year,
            'meta.date' => formatDate(now()),
        ];
    }

    /**
     * Set an entity for tag resolution.
     */
    public function setEntity(string $entityType, Model $entity): self
    {
        $this->entities[$entityType] = $entity;

        return $this;
    }

    /**
     * Set multiple entities at once.
     *
     * @param  array<string, Model>  $entities
     */
    public function setEntities(array $entities): self
    {
        foreach ($entities as $type => $entity) {
            $this->setEntity($type, $entity);
        }

        return $this;
    }

    /**
     * Set a context variable.
     */
    public function setContextVariable(string $key, mixed $value): self
    {
        $this->contextVariables[$key] = (string) $value;

        return $this;
    }

    /**
     * Set multiple context variables.
     *
     * @param  array<string, mixed>  $variables
     */
    public function setContextVariables(array $variables): self
    {
        foreach ($variables as $key => $value) {
            $this->setContextVariable($key, $value);
        }

        return $this;
    }

    /**
     * Set a global tag override.
     */
    public function setGlobalTag(string $key, string $value): self
    {
        $this->globalTags[$key] = $value;

        return $this;
    }

    /**
     * Resolve all merge tags in content.
     */
    public function resolve(string $content): string
    {
        $content = $this->resolveRawTags($content);

        return $this->resolveEscapedTags($content);
    }

    /**
     * Resolve raw tags (triple braces).
     */
    protected function resolveRawTags(string $content): string
    {
        return preg_replace_callback(
            self::RAW_TAG_PATTERN,
            fn ($matches) => $this->resolveTag($matches[1], escape: false),
            $content,
        );
    }

    /**
     * Resolve escaped tags (double braces).
     */
    protected function resolveEscapedTags(string $content): string
    {
        return preg_replace_callback(
            self::ESCAPED_TAG_PATTERN,
            fn ($matches) => $this->resolveTag($matches[1], escape: true),
            $content,
        );
    }

    /**
     * Resolve a single tag to its value.
     */
    protected function resolveTag(string $tag, bool $escape = true): string
    {
        $parsed = $this->parseTag($tag);

        if ($parsed === null) {
            return $this->formatUnresolvedTag($tag, $escape);
        }

        $value = $this->getTagValue($parsed['prefix'], $parsed['key']);

        if ($value === null) {
            return $this->formatUnresolvedTag($tag, $escape);
        }

        return $escape ? e($value) : $value;
    }

    /**
     * Parse a tag into prefix and key.
     *
     * @return array{prefix: string, key: string}|null
     */
    protected function parseTag(string $tag): ?array
    {
        $parts = \explode('.', $tag, 2);

        if (\count($parts) !== 2) {
            return null;
        }

        return ['prefix' => $parts[0], 'key' => $parts[1]];
    }

    /**
     * Format an unresolved tag as placeholder.
     */
    protected function formatUnresolvedTag(string $tag, bool $escape): string
    {
        return $escape ? "{{ {$tag} }}" : "{{{ {$tag} }}}";
    }

    /**
     * Get the value for a tag.
     */
    protected function getTagValue(string $prefix, string $key): ?string
    {
        return $this->getGlobalTagValue($prefix, $key)
            ?? $this->getContextVariableValue($prefix, $key)
            ?? $this->getEntityTagValue($prefix, $key);
    }

    /**
     * Get value from global tags.
     */
    protected function getGlobalTagValue(string $prefix, string $key): ?string
    {
        $globalKey = "{$prefix}.{$key}";

        return $this->globalTags[$globalKey] ?? null;
    }

    /**
     * Get value from context variables.
     */
    protected function getContextVariableValue(string $prefix, string $key): ?string
    {
        if ($prefix !== 'action') {
            return null;
        }

        return $this->contextVariables[$key] ?? null;
    }

    /**
     * Get value from entity attributes.
     */
    protected function getEntityTagValue(string $prefix, string $key): ?string
    {
        if (! isset($this->entities[$prefix])) {
            return null;
        }

        $entity = $this->entities[$prefix];

        if (! $this->entityHasAttribute($entity, $key)) {
            return null;
        }

        return $this->columnDiscovery->resolveTagFromModel($entity, $key);
    }

    /**
     * Check if entity has the specified attribute.
     */
    protected function entityHasAttribute(Model $entity, string $key): bool
    {
        return array_key_exists($key, $entity->getAttributes())
            || $entity->hasGetMutator($key);
    }

    /**
     * Extract all tags from content.
     *
     * @return array<string>
     */
    public function extractTags(string $content): array
    {
        preg_match_all(self::EXTRACT_TAG_PATTERN, $content, $matches);

        return ! empty($matches[1]) ? array_unique($matches[1]) : [];
    }

    /**
     * Validate tags in content.
     *
     * @param  array<string>  $entityTypes
     * @param  array<string>  $contextVariableKeys
     * @return array<string> Invalid tags
     */
    public function validateTags(string $content, array $entityTypes, array $contextVariableKeys = []): array
    {
        $usedTags = $this->extractTags($content);

        return array_filter(
            $usedTags,
            fn ($tag) => ! $this->isValidTag($tag, $entityTypes, $contextVariableKeys),
        );
    }

    /**
     * Check if a tag is valid.
     *
     * @param  array<string>  $entityTypes
     * @param  array<string>  $contextVariableKeys
     */
    public function isValidTag(string $tag, array $entityTypes, array $contextVariableKeys = []): bool
    {
        $parsed = $this->parseTag($tag);

        if ($parsed === null) {
            return false;
        }

        return $this->isGlobalTag($tag)
            || $this->isContextTag($parsed['prefix'], $parsed['key'], $contextVariableKeys)
            || $this->isEntityTag($parsed['prefix'], $tag, $entityTypes);
    }

    /**
     * Check if tag is a global tag.
     */
    protected function isGlobalTag(string $tag): bool
    {
        return isset($this->globalTags[$tag]);
    }

    /**
     * Check if tag is a context variable.
     *
     * @param  array<string>  $contextVariableKeys
     */
    protected function isContextTag(string $prefix, string $key, array $contextVariableKeys): bool
    {
        return $prefix === 'action' && \in_array($key, $contextVariableKeys, true);
    }

    /**
     * Check if tag is a valid entity tag.
     *
     * @param  array<string>  $entityTypes
     */
    protected function isEntityTag(string $prefix, string $tag, array $entityTypes): bool
    {
        if (! \in_array($prefix, $entityTypes, true)) {
            return false;
        }

        $modelClass = $this->entityRegistry->getModelClass($prefix);

        if ($modelClass === null) {
            return false;
        }

        $tags = $this->columnDiscovery->getTagsForModel($modelClass, $prefix);

        return isset($tags[$tag]);
    }

    /**
     * Get all available tags for entity types.
     *
     * @param  array<string>  $entityTypes
     * @param  array<string>  $contextVariableKeys
     * @return array<string, array<string, array{label: string, type: string, example: string}>>
     */
    public function getAvailableTags(array $entityTypes, array $contextVariableKeys = []): array
    {
        return [
            'global' => $this->buildGlobalTagsMeta(),
            ...($contextVariableKeys ? ['action' => $this->buildContextTagsMeta($contextVariableKeys)] : []),
            ...$this->buildEntityTagsMeta($entityTypes),
        ];
    }

    /**
     * Build metadata for global tags.
     *
     * @return array<string, array{label: string, type: string, example: string}>
     */
    protected function buildGlobalTagsMeta(): array
    {
        $meta = [];
        foreach ($this->globalTags as $key => $value) {
            $meta[$key] = [
                'label' => $this->humanizeTagKey($key),
                'type' => 'string',
                'example' => $value,
            ];
        }

        return $meta;
    }

    /**
     * Build metadata for context variables.
     *
     * @param  array<string>  $contextVariableKeys
     * @return array<string, array{label: string, type: string, example: string}>
     */
    protected function buildContextTagsMeta(array $contextVariableKeys): array
    {
        $meta = [];
        foreach ($contextVariableKeys as $key) {
            $meta["action.{$key}"] = [
                'label' => $this->humanizeTagKey($key),
                'type' => 'url',
                'example' => "https://example.com/action/{$key}",
            ];
        }

        return $meta;
    }

    /**
     * Build metadata for entity tags.
     *
     * @param  array<string>  $entityTypes
     * @return array<string, array<string, array{label: string, type: string, example: string}>>
     */
    protected function buildEntityTagsMeta(array $entityTypes): array
    {
        $meta = [];
        foreach ($entityTypes as $entityType) {
            $tags = $this->columnDiscovery->getTagsForEntityType($entityType);
            if (! empty($tags)) {
                $meta[$entityType] = $tags;
            }
        }

        return $meta;
    }

    /**
     * Convert tag key to human-readable label.
     */
    protected function humanizeTagKey(string $key): string
    {
        return \str_replace(['.', '_'], ' ', ucwords($key, '._'));
    }

    /**
     * Reset engine state.
     */
    public function reset(): self
    {
        $this->entities = [];
        $this->contextVariables = [];
        $this->initializeGlobalTags();

        return $this;
    }
}
