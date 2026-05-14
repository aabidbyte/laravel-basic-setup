<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Tenant;
use App\Services\Tenancy\TestingTenantDatabaseManager;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\InteractsWithTenancy;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;
    use InteractsWithTenancy;

    /**
     * The connections that should be transacted.
     *
     * @var array<int, string>
     */
    protected array $connectionsToTransact = ['mysql', 'central'];

    protected $seed = true;

    public function runDatabaseMigrations(): void
    {
        $this->beforeRefreshingDatabase();
        $this->refreshTestDatabase();
        $this->afterRefreshingDatabase();

        $this->app[Kernel::class]->setArtisan(null);
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

    protected function deleteTestingTenants(): void
    {
        Tenant::query()
            ->get()
            ->each(function (Tenant $tenant): void {
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

    private function testing_databases(): TestingTenantDatabaseManager
    {
        return $this->app->make(TestingTenantDatabaseManager::class);
    }

    protected function tearDown(): void
    {
        if (\function_exists('tenancy')) {
            tenancy()->end();
        }

        parent::tearDown();
    }
}
