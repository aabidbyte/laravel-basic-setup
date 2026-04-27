<?php

namespace App\Providers;

use App\Console\Commands\Database\Migrations\MigrateAll;
use App\Console\Commands\Database\WipeTestDatabases;
use App\Enums\Database\ConnectionType;
use App\Services\Database\DatabaseService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole() && $this->app->environment('testing')) {
            $this->configureTestingDatabaseConnections($this->app->make(DatabaseService::class)->getParallelTestingToken());

            ParallelTesting::setUpProcess(function (int|string $token) {
                $this->setParallelTestingToken((string) $token);
                $this->configureTestingDatabaseConnections((string) $token);

                try {
                    Artisan::call(MigrateAll::class, [
                        '--fresh' => true,
                        '--force' => true,
                        '--seed' => true,
                    ]);
                } catch (Throwable $e) {
                    Log::error('ParallelTesting MigrateAll Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                    throw $e;
                }
            });

            ParallelTesting::tearDownProcess(function () {
                Artisan::call(WipeTestDatabases::class, ['--force' => true]);
            });
        }
    }

    protected function configureTestingDatabaseConnections(?string $token = null): void
    {
        /** @var DatabaseService $databaseService */
        $databaseService = $this->app->make(DatabaseService::class);
        $masterName = (string) config('app.name');
        $tenantName = (string) (\collect(config('tenancy.tenants', []))->first() ?? 'test tenant 1');

        config([
            'database.connections.' . ConnectionType::LANDLORD->connectionName() . '.database' => $databaseService->generateTestingLandlordDatabaseName($token),
            'database.connections.' . ConnectionType::MASTER->connectionName() . '.database' => $databaseService->generateTestingMasterDatabaseName($masterName, $token),
            'database.connections.' . ConnectionType::TENANT->connectionName() . '.database' => $databaseService->generateTestingTenantDatabaseName($masterName, $tenantName, $token),
        ]);

        $databaseService->purgeConnections([
            ConnectionType::LANDLORD->connectionName(),
            ConnectionType::MASTER->connectionName(),
            ConnectionType::TENANT->connectionName(),
        ]);
    }

    protected function setParallelTestingToken(string $token): void
    {
        putenv("TEST_TOKEN={$token}");
        $_ENV['TEST_TOKEN'] = $token;
        $_SERVER['TEST_TOKEN'] = $token;
    }
}
