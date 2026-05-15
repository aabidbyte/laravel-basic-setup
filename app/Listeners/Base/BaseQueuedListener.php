<?php

declare(strict_types=1);

namespace App\Listeners\Base;

use App\Support\Tenancy\TenantRuntime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

abstract class BaseQueuedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function viaConnection(): string
    {
        return TenantRuntime::DATABASE_QUEUE_CONNECTION;
    }

    public function viaQueue(): string
    {
        return TenantRuntime::DEFAULT_QUEUE;
    }
}
