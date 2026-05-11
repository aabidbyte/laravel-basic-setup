<?php

declare(strict_types=1);

namespace App\Jobs\Tenancy;

use App\Models\Tenant;
use Stancl\Tenancy\Jobs\SeedDatabase;

class SeedDatabaseIfRequested
{
    /**
     * Execute the job.
     */
    public function handle(Tenant $tenant): void
    {
        // Check if seeding is requested.
        if ($tenant->should_seed) {
            (new SeedDatabase($tenant))->handle();
        }
    }
}
