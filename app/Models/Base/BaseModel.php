<?php

namespace App\Models\Base;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Base model class for all application models.
 *
 * This model includes the HasUuid trait to ensure all models
 * automatically generate unique UUIDs when created.
 *
 * All new models should extend this class instead of Eloquent\Model directly.
 */
abstract class BaseModel extends EloquentModel
{
    use HasUuid;
}
