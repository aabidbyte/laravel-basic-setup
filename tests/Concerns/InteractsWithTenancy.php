<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\DatabaseTransactionsManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait InteractsWithTenancy
{
    /**
     * The tenant used for testing.
     */
    protected ?Tenant $tenant = null;

    protected bool $tenantTransactionStarted = false;

    protected ?ConnectionInterface $tenantTransactionConnection = null;

    /**
     * Initialize tenancy for the test.
     */
    public function setUpTenancy(?Tenant $tenant = null): void
    {
        if ($tenant) {
            $this->tenant = $tenant;
        }

        if (! $this->tenant) {
            $this->tenant = $this->createReusableTestingTenant();
        }

        $domain = $this->tenant->domains()->first()->domain;

        tenancy()->initialize($this->tenant);
        $this->beginTestingTenantTransaction();

        // Ensure all URL generation uses the tenant domain
        $this->app['config']->set('app.url', 'http://' . $domain);
        URL::forceRootUrl('http://' . $domain);
        $this->withHeader('Host', $domain);
    }

    /**
     * Run a callback within a tenant context.
     */
    protected function inTenantContext(Tenant $tenant, callable $callback)
    {
        $previousTenant = tenant();

        tenancy()->initialize($tenant);
        $result = $callback();

        if ($previousTenant) {
            tenancy()->initialize($previousTenant);
        } else {
            tenancy()->end();
        }

        return $result;
    }

    protected function finishTestingTenantTransaction(): void
    {
        if (! $this->tenantTransactionStarted) {
            return;
        }

        $connection = $this->tenantTransactionConnection ?? DB::connection('tenant');
        $dispatcher = $connection->getEventDispatcher();

        $connection->unsetEventDispatcher();

        if ($connection->getPdo()?->inTransaction()) {
            $connection->rollBack();
        }

        $connection->setEventDispatcher($dispatcher);
        $connection->disconnect();

        $this->tenantTransactionStarted = false;
        $this->tenantTransactionConnection = null;
    }

    private function createReusableTestingTenant(): Tenant
    {
        $tenantId = 'tenant-' . Str::random(12);
        $tenantDatabase = $this->testing_databases()->prepareReusableTenantDatabase();

        return Tenant::withoutEvents(function () use ($tenantId, $tenantDatabase): Tenant {
            $tenant = Tenant::factory()->create([
                'tenant_id' => $tenantId,
                'slug' => $tenantId,
                'should_seed' => false,
            ]);

            $tenant->setInternal('db_name', $tenantDatabase);
            $tenant->save();

            $tenant->domains()->create([
                'domain' => "{$tenantId}.test",
            ]);

            return $tenant;
        });
    }

    private function beginTestingTenantTransaction(): void
    {
        if ($this->tenantTransactionStarted) {
            return;
        }

        $connection = DB::connection('tenant');
        $this->configureTenantTransactionManager($connection);

        $dispatcher = $connection->getEventDispatcher();

        $connection->unsetEventDispatcher();
        $connection->beginTransaction();
        $connection->setEventDispatcher($dispatcher);

        $this->tenantTransactionStarted = true;
        $this->tenantTransactionConnection = $connection;
    }

    private function configureTenantTransactionManager(ConnectionInterface $connection): void
    {
        $transactionsManager = $this->app->bound('db.transactions')
            ? $this->app->make('db.transactions')
            : new DatabaseTransactionsManager(['tenant']);

        $connection->setTransactionManager($transactionsManager);
    }
}
