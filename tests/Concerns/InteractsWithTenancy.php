<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait InteractsWithTenancy
{
    /**
     * The tenant used for testing.
     */
    protected ?Tenant $tenant = null;

    /**
     * Initialize tenancy for the test.
     */
    public function setUpTenancy(?Tenant $tenant = null): void
    {
        if ($tenant) {
            $this->tenant = $tenant;
        }

        if (! $this->tenant) {
            $this->tenant = Tenant::factory()->create([
                'id' => 'tenant-' . Str::random(12),
            ]);
            $this->tenant->domains()->create([
                'domain' => $this->tenant->tenant_id . '.test',
            ]);
        }

        $domain = $this->tenant->domains()->first()->domain;

        tenancy()->initialize($this->tenant);

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
}
