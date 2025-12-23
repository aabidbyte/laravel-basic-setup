<?php

declare(strict_types=1);

namespace App\Services\DataTable\Contracts;

/**
 * Contract for DataTable transformer classes
 *
 * Defines the structure for transforming Eloquent models
 * into arrays suitable for DataTable display.
 */
interface TransformerInterface
{
    /**
     * Transform model for DataTable response
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array<string, mixed>
     */
    public function transform($model): array;
}
