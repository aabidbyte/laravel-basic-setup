<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Tenant;
use App\Services\Tenancy\TestingTenantDatabaseManager;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\UsesMigratedTestDatabases;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions, UsesMigratedTestDatabases {
        UsesMigratedTestDatabases::connectionsToTransact insteadof DatabaseTransactions;
    }
    use InteractsWithTenancy;

    /**
     * The connections that should be transacted.
     *
     * @var array<int, string>
     */
    protected array $connectionsToTransact = ['mysql', 'central'];

    protected $seed = true;

    public function beginDatabaseTransaction(): void
    {
        //
    }

    public function createApplication()
    {
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $app->make(TestingTenantDatabaseManager::class)->configure();

        return $app;
    }

    protected function beforeRefreshingDatabase()
    {
        $this->configureTestingDatabaseIsolation();
    }

    protected function configureTestingDatabaseIsolation(): void
    {
        $this->testing_databases()->configure();
    }

    protected function dropTestingTenantDatabases(): void
    {
        $this->testing_databases()->dropForDatabase();
    }

    protected function cleanUpTestingTenancy(): void
    {
        if (! $this->hasTestingTenants()) {
            return;
        }

        $this->deleteTestingTenants();
        $this->dropTestingTenantDatabases();
    }

    protected function hasTestingTenants(): bool
    {
        try {
            return Tenant::query()->exists();
        } catch (QueryException) {
            return false;
        }
    }

    protected function deleteTestingTenants(): void
    {
        Tenant::query()
            ->get()
            ->each(function (Tenant $tenant): void {
                if ($tenant->getInternal('db_name') === $this->testing_databases()->reusableTenantDatabaseName()) {
                    Tenant::withoutEvents(fn (): ?bool => $tenant->delete());

                    return;
                }

                try {
                    $tenant->delete();
                } catch (QueryException $exception) {
                    if ($exception->getCode() !== 'HY000') {
                        throw $exception;
                    }

                    Tenant::withoutEvents(fn (): ?bool => $tenant->delete());
                }
            });
    }

    protected function testing_databases(): TestingTenantDatabaseManager
    {
        return $this->app->make(TestingTenantDatabaseManager::class);
    }

    protected function tearDown(): void
    {
        $this->finishTestingTenantTransaction();

        if (\function_exists('tenancy')) {
            tenancy()->end();
        }

        $this->cleanUpTestingTenancy();

        parent::tearDown();
    }
}
