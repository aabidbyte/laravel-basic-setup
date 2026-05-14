<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Tenancy\TestingTenantDatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! isProduction());

        $this->configureTestingDatabaseIsolation();
        $this->configureParallelTestingDatabaseIsolation();
    }

    private function configureTestingDatabaseIsolation(): void
    {
        $this->testingTenantDatabaseManager()->configure();
    }

    private function configureParallelTestingDatabaseIsolation(): void
    {
        ParallelTesting::setUpTestDatabaseBeforeMigrating(function (string $database): void {
            $this->testingTenantDatabaseManager()->configure($database);
        });

        ParallelTesting::setUpTestCase(function (): void {
            $this->configureTestingDatabaseIsolation();
        });

        ParallelTesting::tearDownProcess(function (int $token): void {
            $this->testingTenantDatabaseManager()->dropForDatabase(
                $this->testingTenantDatabaseManager()->parallelDatabaseName($token),
            );
        });
    }

    private function testingTenantDatabaseManager(): TestingTenantDatabaseManager
    {
        return $this->app->make(TestingTenantDatabaseManager::class);
    }
}
