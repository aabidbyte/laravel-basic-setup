<?php

namespace App\Models\Concerns;

use App\Enums\Database\ConnectionType;

trait UsesLandlordConnection
{
    /**
     * Get the database connection for the model.
     */
    public function getConnectionName()
    {
        return databaseService()->createDynamicConnection(
            databaseService()->generateLandlordDatabaseName(),
            ConnectionType::LANDLORD,
        );
    }
}
