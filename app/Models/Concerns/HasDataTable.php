<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\DataTable\Dsl\DataTableDefinition;

/**
 * Trait for models that support DataTable definitions
 *
 * Provides a static method to define DataTable configuration using the fluent DSL.
 */
trait HasDataTable
{
    /**
     * Get the DataTable definition for this model
     *
     * Override this method in the model to define the DataTable configuration.
     */
    public static function datatable(): DataTableDefinition
    {
        return DataTableDefinition::make();
    }
}
