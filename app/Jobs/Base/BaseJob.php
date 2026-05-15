<?php

declare(strict_types=1);

namespace App\Jobs\Base;

use App\Support\Tenancy\TenantRuntime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onConnection($this->queueConnection());
        $this->onQueue($this->queueName());
    }

    protected function queueConnection(): string
    {
        return TenantRuntime::DATABASE_QUEUE_CONNECTION;
    }

    protected function queueName(): string
    {
        return TenantRuntime::DEFAULT_QUEUE;
    }
}
