<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\Database\ConnectionType;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\CachedState;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\Attributes\UseDb;
use Tests\Attributes\UseMasterDb;
use Tests\Support\MultiTenancyTestCase;
use Tests\Traits\UsesMasterDb;
use Tests\Traits\UsesTenantDb;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use MultiTenancyTestCase;

    public function createApplication()
    {
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $this->traitsUsedByTest = \array_flip(class_uses_recursive(static::class));

        if (isset(CachedState::$cachedConfig) &&
            isset($this->traitsUsedByTest[WithCachedConfig::class])) {
            $this->markConfigCached($app);
        }

        if (isset(CachedState::$cachedRoutes) &&
            isset($this->traitsUsedByTest[WithCachedRoutes::class])) {
            $app->booting(fn () => $this->markRoutesCached($app));
        }

        $app->make(Kernel::class)->bootstrap();
        $this->app = $app;

        if ($app->environment('testing')) {
            $this->setupMultiTenancyTests();
        }

        return $app;
    }

    protected function setUpTraits()
    {
        $this->applyConfiguredDatabaseConnection();

        return parent::setUpTraits();
    }

    protected function applyConfiguredDatabaseConnection(): void
    {
        $connection = $this->resolveDatabaseConnection();

        config(['database.default' => $connection]);
        DB::setDefaultConnection($connection);

        // Safety: Override base connection configs to ensure explicit connection calls (e.g., DB::connection('landlord'))
        // do not accidentally hit production databases.
        $testLandlordDb = databaseService()->generateLandlordDatabaseName();
        $landlordConnectionName = databaseService()->configureConnection($testLandlordDb, ConnectionType::LANDLORD);

        config([
            'database.connections.' . ConnectionType::LANDLORD->value => config("database.connections.{$landlordConnectionName}"),
        ]);

        databaseService()->purgeConnections([
            ConnectionType::LANDLORD->value,
            ConnectionType::MASTER->value,
            ConnectionType::TENANT->value,
        ]);
    }

    protected function resolveDatabaseConnection(): string
    {
        $connectionTypeStr = config('database.default', ConnectionType::LANDLORD->value);
        $connectionType = ConnectionType::tryFrom($connectionTypeStr) ?? ConnectionType::LANDLORD;

        $class = new ReflectionClass($this);
        $testName = $this->name();
        $method = $class->hasMethod($testName) ? $class->getMethod($testName) : null;

        if ($method !== null) {
            $connectionType = $this->resolveConnectionFromReflector($method, $connectionType);
        }

        $connectionType = $this->resolveConnectionFromReflector($class, $connectionType);

        $testSlug = 'test';

        $dbName = match ($connectionType) {
            ConnectionType::MASTER => databaseService()->generateMasterDatabaseName($testSlug),
            ConnectionType::TENANT => databaseService()->generateTenantDatabaseName($testSlug, $testSlug),
            default => databaseService()->generateLandlordDatabaseName(),
        };

        return databaseService()->configureConnection($dbName, $connectionType);
    }

    protected function resolveConnectionFromReflector(ReflectionClass|ReflectionMethod $reflector, ConnectionType $fallback): ConnectionType
    {
        if ($reflector->getAttributes(UseMasterDb::class) !== [] || in_array(UsesMasterDb::class, class_uses_recursive($this), true)) {
            return ConnectionType::MASTER;
        }

        if (in_array(UsesTenantDb::class, class_uses_recursive($this), true)) {
            return ConnectionType::TENANT;
        }

        $useDbAttributes = $reflector->getAttributes(UseDb::class);

        if ($useDbAttributes === []) {
            return $fallback;
        }

        /** @var UseDb $attribute */
        $attribute = $useDbAttributes[0]->newInstance();

        if ($attribute->connection instanceof ConnectionType) {
            return $attribute->connection;
        }

        return ConnectionType::tryFrom((string) $attribute->connection) ?? $fallback;
    }
}
