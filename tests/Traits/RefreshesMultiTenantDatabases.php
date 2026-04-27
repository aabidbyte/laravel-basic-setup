<?php

namespace Tests\Traits;

use App\Console\Commands\Database\Migrations\MigrateAll;
use App\Console\Commands\Database\WipeTestDatabases;
use Illuminate\Support\Facades\Artisan;

/**
 * Trait RefreshesMultiTenantDatabases
 *
 * This trait is used to strictly enforce the application's unique bespoke migration
 * and teardown logic on a per-test basis without relying on simpler Laravel standards.
 */
trait RefreshesMultiTenantDatabases
{
    protected function setUpRefreshesMultiTenantDatabases(): void
    {
        // Execute a fresh wipe and migration before the test runs natively
        Artisan::call(MigrateAll::class, ['--fresh' => true, '--force' => true]);
    }

    protected function tearDownRefreshesMultiTenantDatabases(): void
    {
        // Execute a manual wipe after the test natively
        Artisan::call(WipeTestDatabases::class, ['--force' => true]);
    }
}
