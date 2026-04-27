<?php

declare(strict_types=1);

namespace App\Models\Base;

use App\Models\Concerns\UsesLandlordConnection;

/**
 * Base model class for all landlord-level models.
 *
 * This model ensures that all models associated with the landlord database
 * use the correct database connection and follow standard landlord patterns.
 *
 * Models extending this class should be stored in the 'landlord' database.
 */
abstract class BaseLandlordModel extends BaseModel
{
    use UsesLandlordConnection;
}
