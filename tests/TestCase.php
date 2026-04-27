<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\Database\ConnectionType;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\Attributes\UseDb;
use Tests\Attributes\UseMasterDb;
use Tests\Support\MultiTenancyTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use MultiTenancyTestCase;

    public function createApplication()
    {
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $this->traitsUsedByTest = \array_flip(class_uses_recursive(static::class));

        if (isset(\Illuminate\Foundation\Testing\CachedState::$cachedConfig) &&
            isset($this->traitsUsedByTest[\Illuminate\Foundation\Testing\WithCachedConfig::class])) {
            $this->markConfigCached($app);
        }

        if (isset(\Illuminate\Foundation\Testing\CachedState::$cachedRoutes) &&
            isset($this->traitsUsedByTest[\Illuminate\Foundation\Testing\WithCachedRoutes::class])) {
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

        $token = databaseService()->getParallelTestingToken() ?: '1';
        $testSlug = "test_{$token}";

        $dbName = match ($connectionType) {
            ConnectionType::MASTER => databaseService()->generateMasterDatabaseName($testSlug),
            ConnectionType::TENANT => databaseService()->generateTenantDatabaseName($testSlug, $testSlug),
            default => databaseService()->generateLandlordDatabaseName(),
        };

        return databaseService()->configureConnection($dbName, $connectionType);
    }

    protected function resolveConnectionFromReflector(ReflectionClass|ReflectionMethod $reflector, ConnectionType $fallback): ConnectionType
    {
        if ($reflector->getAttributes(UseMasterDb::class) !== [] || in_array(\Tests\Traits\UsesMasterDb::class, class_uses_recursive($this), true)) {
            return ConnectionType::MASTER;
        }

        if (in_array(\Tests\Traits\UsesTenantDb::class, class_uses_recursive($this), true)) {
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
