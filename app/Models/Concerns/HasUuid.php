<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Trait for models that require automatic UUID generation.
 *
 * This trait automatically generates a unique UUID for the model when it's being created.
 * The UUID is generated using Laravel's Str::uuid() helper and ensures uniqueness
 * by checking the database before assigning.
 */
trait HasUuid
{
    /**
     * Boot the trait.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            // Check if UUID is already set (via mass assignment or direct assignment)
            $uuid = $model->getRawOriginal('uuid')
                ?? $model->getAttribute('uuid')
                ?? ($model->attributes['uuid'] ?? null);

            if (empty($uuid)) {
                $model->uuid = static::generateUniqueUuid($model);
            }
        });
    }

    /**
     * Generate a unique UUID for the model.
     */
    protected static function generateUniqueUuid(Model $model): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $uuid = (string) Str::uuid();

            $exists = $model->newQuery()
                ->where('uuid', $uuid)
                ->exists();

            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            throw new RuntimeException('Unable to generate unique UUID after ' . $maxAttempts . ' attempts.');
        }

        return $uuid;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
