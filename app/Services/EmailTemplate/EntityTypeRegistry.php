<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

use App\Models\Team;
use App\Models\User;

/**
 * Entity Type Registry.
 *
 * Maps entity type names to model classes for merge tag discovery.
 * Add new entities here to make them available for email templates.
 */
class EntityTypeRegistry
{
    /**
     * Map entity type names to model classes.
     *
     * @var array<string, class-string>
     */
    protected array $entities = [
        'user' => User::class,
        'team' => Team::class,
    ];

    /**
     * Map entity types to daisyUI badge colors.
     *
     * @var array<string, string>
     */
    protected array $colors = [
        'user' => 'primary',
        'team' => 'secondary',
        'context' => 'accent', // Virtual entity for context vars
    ];

    /**
     * Get the model class for an entity type.
     *
     * @param  string  $entityType  The entity type name (e.g., 'user', 'team')
     * @return class-string|null The model class or null if not found
     */
    public function getModelClass(string $entityType): ?string
    {
        return $this->entities[$entityType] ?? null;
    }

    /**
     * Get all available entity types.
     *
     * @return array<string>
     */
    public function getAvailableEntityTypes(): array
    {
        return array_keys($this->entities);
    }

    /**
     * Check if an entity type exists.
     */
    public function hasEntityType(string $entityType): bool
    {
        return isset($this->entities[$entityType]);
    }

    /**
     * Register a new entity type.
     *
     * @param  string  $entityType  The entity type name
     * @param  class-string  $modelClass  The model class
     */
    public function register(string $entityType, string $modelClass): void
    {
        $this->entities[$entityType] = $modelClass;
    }

    /**
     * Get entity type options for forms.
     *
     * @return array<string, string> Key => Label pairs
     */
    public function getEntityTypeOptions(): array
    {
        $options = [];
        foreach ($this->entities as $type => $class) {
            $options[$type] = __("types.$type");
        }

        return $options;
    }

    /**
     * Get the table name for an entity type.
     */
    public function getTableName(string $entityType): ?string
    {
        $modelClass = $this->getModelClass($entityType);

        if ($modelClass === null) {
            return null;
        }

        return (new $modelClass)->getTable();
    }

    /**
     * Get the badge color for an entity type.
     */
    public function getColor(string $entityType): string
    {
        return $this->colors[$entityType] ?? 'neutral';
    }
}
