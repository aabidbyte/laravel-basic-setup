# PLAN: Pest Multi-Tenancy Setup

## 1. Context Extracted

Based on our `Context Scan`, here is exactly what we found:

*   **Config (`config/database.php`)**
    *   Connections defined: `landlord`, `master`, `tenant`, and `tests_`.
    *   The exact keys are sourced dynamically from `App\Enums\Database\ConnectionType`.
    *   No third-party packages orchestrate DB swaps here; `DatabaseServiceProvider` boots them.
*   **.ENV settings**
    *   Prefixes used internally in `config/database.php` read from: `DB_LANDLORD_*`, `DB_MASTERS_*`, `DB_TENANTS_*`.
*   **Commands (`app/Console/Commands/Database/Migrations`)**
    *   Found `MigrateAll::class` (Signature: `migrate:all {--fresh} {--seed} {--force}`).
    *   Also found bespoke migration commands for each tier: `MigrateLandlord::class`, `MigrateMasters::class`, `MigrateTenants::class`.
    *   Found `WipeTestDatabases::class` to safely tear down the multi-databases.
*   **Providers & Bootstrapping (`app/Providers/DatabaseServiceProvider.php`)**
    *   Initializes only the `ConnectionType` keys into runtime config.
*   **Constants (`app/Enums/Database/ConnectionType.php`)**
    *   Extremely robust Enum representing `LANDLORD = 'landlord'`, `MASTER = 'master'`, `TENANT = 'tenant'`, `TESTS = 'tests'`.
*   **Tests (`tests/`)**
    *   Found `tests/TestCase.php` using `DatabaseTransactions`.
    *   Found `tests/Pest.php` already booting `uses(TestCase::class)->in('Feature', 'Unit')`.

---

## 2. Proposed Deliverables

The requested files implement attribute-based reflection in `tests/TestCase.php` ensuring that the targeted `ConnectionType` determines `config('database.default')` *before* hitting database calls or sweeping DBs.

### `tests/Attributes/UseMasterDb.php`
```php
<?php

namespace Tests\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class UseMasterDb
{
}
```

### `tests/Attributes/UseDb.php`
```php
<?php

namespace Tests\Attributes;

use App\Enums\Database\ConnectionType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class UseDb
{
    public function __construct(
        public readonly ConnectionType|string $connection = ConnectionType::TENANT
    ) {}
}
```

### `tests/Traits/UsesMasterDb.php`
```php
<?php

namespace Tests\Traits;

use App\Enums\Database\ConnectionType;
use Illuminate\Support\Facades\Config;

trait UsesMasterDb
{
    protected function setUpUsesMasterDb(): void
    {
        Config::set('database.default', ConnectionType::MASTER->value);
    }
}
```

### `tests/Traits/UsesTenantDb.php`
```php
<?php

namespace Tests\Traits;

use App\Enums\Database\ConnectionType;
use Illuminate\Support\Facades\Config;

trait UsesTenantDb
{
    protected function setUpUsesTenantDb(): void
    {
        Config::set('database.default', ConnectionType::TENANT->value);
    }
}
```

### `tests/TestCase.php`
```php
<?php

namespace Tests;

use App\Console\Commands\Database\WipeTestDatabases;
use App\Console\Commands\Database\Migrations\MigrateAll;
use App\Enums\Database\ConnectionType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions; // Note: transactions might negate the need to wipe entirely if used cleanly

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveDatabaseConnection();

        // Calling the correct explicitly found command
        Artisan::call(MigrateAll::class, ['--fresh' => true]);
    }

    protected function tearDown(): void
    {
        Artisan::call(WipeTestDatabases::class, ['--force' => true]);

        parent::tearDown();
    }

    protected function resolveDatabaseConnection(): void
    {
        $connection = ConnectionType::TENANT->value;
        $class = new ReflectionClass($this);
        $method = $class->hasMethod($this->name()) ? $class->getMethod($this->name()) : null;

        // 1. Method-level override check
        if ($method) {
            if (!empty($method->getAttributes(\Tests\Attributes\UseMasterDb::class))) {
                $connection = ConnectionType::MASTER->value;
            } elseif (!empty($useDbAtts = $method->getAttributes(\Tests\Attributes\UseDb::class))) {
                $attr = $useDbAtts[0]->newInstance();
                $connection = $attr->connection instanceof ConnectionType ? $attr->connection->value : $attr->connection;
            }
        }

        // 2. Class-level override check
        if (!empty($class->getAttributes(\Tests\Attributes\UseMasterDb::class))) {
            $connection = ConnectionType::MASTER->value;
        } elseif (!empty($useDbAtts = $class->getAttributes(\Tests\Attributes\UseDb::class))) {
            $attr = $useDbAtts[0]->newInstance();
            $connection = $attr->connection instanceof ConnectionType ? $attr->connection->value : $attr->connection;
        }

        Config::set('database.default', $connection);
    }
}
```

### `tests/Pest.php`
```php
<?php

use Tests\Traits\UsesMasterDb;
use Tests\Traits\UsesTenantDb;

pest()->extend(Tests\TestCase::class)
    ->in('Feature', 'Unit');

// Uses mapped traits over directories
pest()->uses(UsesMasterDb::class)->in('Feature/Master');
pest()->uses(UsesTenantDb::class)->in('Feature/Tenant');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function something()
{
    // ..
}
```

### `tests/Feature/Master/ExampleMasterTest.php`
```php
<?php

use Tests\Attributes\UseDb;
use App\Enums\Database\ConnectionType;

it('uses the master database connection implicitly due to directory', function () {
    expect(\Illuminate\Support\Facades\Config::get('database.default'))
        ->toBe(ConnectionType::MASTER->value);
});

// Using attributes on test execution in Pest requires PHPUnit #[UseDb] annotations on underlying closures
// or wrapping via test class if you write class-based Pest features.
// Typically in Pest, we attach them as class-level if it's pure Pest structure, or method level on PHPUnit traits.

// #[UseDb('tenant')]
it('overrides to tenant using attribute', function () {
    expect(\Illuminate\Support\Facades\Config::get('database.default'))
        ->toBe(ConnectionType::TENANT->value);
});
```

### `tests/Feature/Tenant/ExampleTenantTest.php`
```php
<?php

use Tests\Attributes\UseMasterDb;
use App\Enums\Database\ConnectionType;

it('uses the tenant database connection implicitly', function () {
    expect(\Illuminate\Support\Facades\Config::get('database.default'))
        ->toBe(ConnectionType::TENANT->value);
});

// #[UseMasterDb]
it('overrides to master using attribute', function () {
    expect(\Illuminate\Support\Facades\Config::get('database.default'))
        ->toBe(ConnectionType::MASTER->value);
});
```
