<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\Database\DatabaseService;

/**
 * Trait to manage multi-tenancy database isolation during tests.
 * This trait ensures that Landlord, Masters, and Tenants connections are
 * isolated using full MySQL databases instead of SQLite.
 */
trait MultiTenancyTestCase
{
    /**
     * Initial Landlord DB for tests.
     */
    protected string $testLandlordDb = 'test_landlord';

    /**
     * Flag to ensure migrations run only once per process.
     */
    protected static bool $migrated = false;

    /**
     * Setup multi-tenancy test environment.
     */
    protected function setupMultiTenancyTests(): void
    {
        $this->testLandlordDb = databaseService()->generateLandlordDatabaseName();

        DatabaseService::setLandlordDatabaseNameOverride($this->testLandlordDb);

        if (! self::$migrated) {
            $this->refreshLandlordDatabase();
            self::$migrated = true;
        }
    }

    /**
     * Run migrations and seeds on the Landlord test database.
     * This will also seed tenants via SampleTenantSeeder.
     */
    protected function refreshLandlordDatabase(): void
    {
        $this->artisan('migrate:all', [
            '--fresh' => true,
            '--force' => true,
            '--seed' => true,
        ]);
    }
}
