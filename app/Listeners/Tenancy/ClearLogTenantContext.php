<?php

declare(strict_types=1);

namespace App\Listeners\Tenancy;

use Illuminate\Support\Facades\Context;
use Stancl\Tenancy\Events\TenancyEnded;

class ClearLogTenantContext
{
    /**
     * Handle the event.
     */
    public function handle(TenancyEnded $event): void
    {
        Context::forget('tenant_id');
    }
}
