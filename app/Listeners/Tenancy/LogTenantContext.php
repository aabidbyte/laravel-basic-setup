<?php

declare(strict_types=1);

namespace App\Listeners\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Context;
use Stancl\Tenancy\Events\TenancyInitialized;

class LogTenantContext
{
    /**
     * Handle the event.
     */
    public function handle(TenancyInitialized $event): void
    {
        $tenant = \tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        Context::add('tenant_id', $tenant->getTenantKey());

        if ($tenant->slug) {
            Context::add('tenant_slug', $tenant->slug);
            Context::add('tenant_log_key', $tenant->logDirectoryName());
        }
    }
}
