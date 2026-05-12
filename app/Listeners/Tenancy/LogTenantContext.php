<?php

declare(strict_types=1);

namespace App\Listeners\Tenancy;

use Illuminate\Support\Facades\Context;
use Stancl\Tenancy\Events\TenancyInitialized;

class LogTenantContext
{
    /**
     * Handle the event.
     */
    public function handle(TenancyInitialized $event): void
    {
        if ($tenantId = \tenant('id')) {
            Context::add('tenant_id', $tenantId);
        }
    }
}
