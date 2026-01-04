<?php

namespace App\Models\Base;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Base model class for all application models.
 *
 * This model includes the HasUuid trait to ensure all models
 * automatically generate unique UUIDs when created.
 *
 * This model also includes the SoftDeletes trait to ensure all models
 * support soft deletion by default.
 *
 * All new models should extend this class instead of Eloquent\Model directly.
 *
 * @see \App\Models\Base\BaseUserModel For authenticatable models
 */
abstract class BaseModel extends EloquentModel
{
    use HasUuid;
    use SoftDeletes;

    /**
     * Get a human-readable label for this model.
     *
     * Used for notifications and UI display to provide context about the model.
     * All models must implement this method.
     */
    abstract public function label(): string;
}
