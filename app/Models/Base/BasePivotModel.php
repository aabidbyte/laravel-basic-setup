<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Base pivot model class for all application pivot models.
 *
 * This model provides a consistent base for pivot tables that need
 * custom functionality beyond simple many-to-many relationships.
 *
 * All new pivot models should extend this class instead of Eloquent\Pivot directly.
 *
 * @see \App\Models\Base\BaseModel For regular models
 * @see \App\Models\Base\BaseUserModel For authenticatable models
 */
abstract class BasePivotModel extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
