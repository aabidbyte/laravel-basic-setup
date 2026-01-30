<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Column Tag Discovery Service.
 *
 * Automatically discovers available merge tags from database columns
 * for a given entity (model). Filters out sensitive columns and
 * formats values based on column type.
 */
class ColumnTagDiscovery
{
    /**
     * Columns to exclude from tag discovery (sensitive/system columns).
     *
     * @var array<string>
     */
    protected array $excludedColumns = [
        'id',
        'uuid',
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'pending_email_token',
        'pending_email_expires_at',
        'frontend_preferences',
        'notification_preferences',
        'deleted_at',
    ];

    /**
     * Column name patterns to exclude (regex patterns).
     *
     * @var array<string>
     */
    protected array $excludedPatterns = [
        '/.*_token$/',
        '/.*_secret$/',
        '/.*_hash$/',
        '/.*_password$/',
    ];

    /**
     * Allowed column types for tag discovery.
     *
     * @var array<string>
     */
    protected array $allowedTypes = [
        'string',
        'text',
        'integer',
        'bigint',
        'smallint',
        'decimal',
        'float',
        'double',
        'boolean',
        'date',
        'datetime',
        'timestamp',
    ];

    public function __construct(
        protected EntityTypeRegistry $entityRegistry,
    ) {}

    /**
     * Get available tags for an entity type.
     *
     * @param  string  $entityType  The entity type (e.g., 'user', 'team')
     * @return array<string, array{label: string, type: string, example: string}>
     */
    public function getTagsForEntityType(string $entityType): array
    {
        $modelClass = $this->entityRegistry->getModelClass($entityType);

        if ($modelClass === null) {
            return [];
        }

        return $this->getTagsForModel($modelClass, $entityType);
    }

    /**
     * Get available tags for a model class.
     *
     * @param  class-string<Model>  $modelClass
     * @param  string  $entityType  The entity type prefix for tags
     * @return array<string, array{label: string, type: string, example: string}>
     */
    public function getTagsForModel(string $modelClass, string $entityType): array
    {
        $model = new $modelClass;
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        $tags = [];
        foreach ($columns as $column) {
            if ($this->isExcludedColumn($column)) {
                continue;
            }

            $type = $this->getColumnType($table, $column);
            if (! \in_array($type, $this->allowedTypes, true)) {
                continue;
            }

            $tagKey = $entityType . '.' . $column;
            $tags[$tagKey] = [
                'label' => $this->humanizeColumnName($column),
                'type' => $type,
                'example' => $this->getExampleValue($type, $column),
            ];
        }

        return $tags;
    }

    /**
     * Check if a column should be excluded.
     */
    protected function isExcludedColumn(string $column): bool
    {
        // Check direct exclusion list
        if (\in_array($column, $this->excludedColumns, true)) {
            return true;
        }

        // Check patterns
        foreach ($this->excludedPatterns as $pattern) {
            if (\preg_match($pattern, $column)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the column type from the database.
     */
    protected function getColumnType(string $table, string $column): string
    {
        return Schema::getColumnType($table, $column);
    }

    /**
     * Convert column name to human-readable label.
     */
    protected function humanizeColumnName(string $column): string
    {
        return Str::of($column)
            ->replace('_', ' ')
            ->replace('id', 'ID')
            ->title()
            ->toString();
    }

    /**
     * Get an example value for a column type.
     */
    protected function getExampleValue(string $type, string $column): string
    {
        // Special cases based on column name
        if (Str::contains($column, 'email')) {
            return 'user@example.com';
        }
        if (Str::contains($column, 'name')) {
            return 'John Doe';
        }
        if (Str::contains($column, 'username')) {
            return 'johndoe';
        }
        if (Str::contains($column, 'url') || Str::contains($column, 'link')) {
            return 'https://example.com';
        }

        // Type-based examples
        return match ($type) {
            'boolean' => 'Yes',
            'integer', 'bigint', 'smallint' => '42',
            'decimal', 'float', 'double' => '99.99',
            'date' => formatDate(now()),
            'datetime', 'timestamp' => formatDateTime(now()),
            default => 'Example text',
        };
    }

    /**
     * Format a value based on its column type.
     *
     * @param  mixed  $value  The raw value
     * @param  string  $type  The column type
     * @return string The formatted value
     */
    public function formatValue(mixed $value, string $type): string
    {
        if ($value === null) {
            return '';
        }

        return match ($type) {
            'boolean' => $value ? __('common.yes') : __('common.no'),
            'date' => formatDate($value),
            'datetime', 'timestamp' => formatDateTime($value),
            'decimal', 'float', 'double' => number_format((float) $value, 2),
            default => (string) $value,
        };
    }

    /**
     * Resolve a tag value from a model instance.
     *
     * @param  Model  $model  The model instance
     * @param  string  $column  The column name
     * @return string The formatted value
     */
    public function resolveTagFromModel(Model $model, string $column): string
    {
        $table = $model->getTable();
        $type = $this->getColumnType($table, $column);
        $value = $model->getAttribute($column);

        return $this->formatValue($value, $type);
    }

    /**
     * Add a column to the exclusion list.
     */
    public function excludeColumn(string $column): void
    {
        if (! \in_array($column, $this->excludedColumns, true)) {
            $this->excludedColumns[] = $column;
        }
    }

    /**
     * Add a pattern to the exclusion list.
     */
    public function excludePattern(string $pattern): void
    {
        if (! \in_array($pattern, $this->excludedPatterns, true)) {
            $this->excludedPatterns[] = $pattern;
        }
    }
}
