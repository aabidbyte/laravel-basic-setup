<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseTransactionsManager;

trait UsesMigratedTestDatabases
{
    protected static bool $migratedTestingDatabases = false;

    protected function setUpUsesMigratedTestDatabases(): void
    {
        $this->configureTestingDatabaseIsolation();

        if (! static::$migratedTestingDatabases) {
            $this->artisan('migrate:fresh', [
                '--drop-views' => false,
                '--drop-types' => false,
                '--seed' => true,
            ]);

            $this->testing_databases()->prepareReusableTenantDatabase();

            $this->app[Kernel::class]->setArtisan(null);

            static::$migratedTestingDatabases = true;
        }

        $this->beginTestingDatabaseTransactions();
    }

    protected function beginTestingDatabaseTransactions(): void
    {
        $database = $this->app->make('db');
        $connections = $this->connectionsToTransact();

        $this->app->instance('db.transactions', $transactionsManager = new DatabaseTransactionsManager($connections));

        foreach ($connections as $name) {
            $connection = $database->connection($name);
            $connection->setTransactionManager($transactionsManager);

            $dispatcher = $connection->getEventDispatcher();

            $connection->unsetEventDispatcher();
            $connection->beginTransaction();
            $connection->setEventDispatcher($dispatcher);
        }

        $this->beforeApplicationDestroyed(function () use ($database): void {
            foreach ($this->connectionsToTransact() as $name) {
                $connection = $database->connection($name);
                $dispatcher = $connection->getEventDispatcher();

                $connection->unsetEventDispatcher();

                if ($connection->getPdo()?->inTransaction()) {
                    $connection->rollBack();
                }

                $connection->setEventDispatcher($dispatcher);
                $connection->disconnect();
            }
        });
    }

    /**
     * @return array<int, string>
     */
    protected function connectionsToTransact(): array
    {
        return property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact
            : [\config('database.default')];
    }
}
