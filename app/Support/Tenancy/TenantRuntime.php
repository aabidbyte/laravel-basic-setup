<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

final class TenantRuntime
{
    public const string CENTRAL_DATABASE_CONNECTION = 'central';

    public const string TENANT_DATABASE_CONNECTION = 'tenant';

    public const string DATABASE_QUEUE_CONNECTION = 'database';

    public const string CENTRAL_DATABASE_QUEUE_CONNECTION = 'central_database';

    public const string REDIS_QUEUE_CONNECTION = 'redis';

    public const string DEFAULT_QUEUE = 'default';

    public const string SESSIONS_TABLE = 'sessions';

    public const string JOBS_TABLE = 'jobs';

    public const string JOB_BATCHES_TABLE = 'job_batches';

    public const string FAILED_JOBS_TABLE = 'failed_jobs';
}
