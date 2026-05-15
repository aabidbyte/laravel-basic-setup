<?php

declare(strict_types=1);

use App\Support\Tenancy\TenantRuntime;
use Illuminate\Support\Facades\Schema;

test('tenant databases include runtime storage tables', function () {
    asTenant();

    foreach (tenantRuntimeTables() as $table) {
        expect(Schema::connection(TenantRuntime::TENANT_DATABASE_CONNECTION)->hasTable($table))->toBeTrue();
    }
});

test('tenant context stores sessions and queue metadata in tenant database', function () {
    asTenant();

    expect(config('session.connection'))->toBe(TenantRuntime::TENANT_DATABASE_CONNECTION)
        ->and(config('queue.connections.database.connection'))->toBe(TenantRuntime::TENANT_DATABASE_CONNECTION)
        ->and(config('queue.batching.database'))->toBe(TenantRuntime::TENANT_DATABASE_CONNECTION)
        ->and(config('queue.failed.database'))->toBe(TenantRuntime::TENANT_DATABASE_CONNECTION);
});

test('tenant runtime configuration reverts to central context', function () {
    asTenant();

    tenancy()->end();

    expect(config('session.connection'))->toBe(TenantRuntime::CENTRAL_DATABASE_CONNECTION)
        ->and(config('queue.connections.database.connection'))->toBe(TenantRuntime::CENTRAL_DATABASE_CONNECTION)
        ->and(config('queue.batching.database'))->toBe(TenantRuntime::CENTRAL_DATABASE_CONNECTION)
        ->and(config('queue.failed.database'))->toBe(TenantRuntime::CENTRAL_DATABASE_CONNECTION);
});

/**
 * @return array<int, string>
 */
function tenantRuntimeTables(): array
{
    return [
        TenantRuntime::SESSIONS_TABLE,
        TenantRuntime::JOBS_TABLE,
        TenantRuntime::JOB_BATCHES_TABLE,
        TenantRuntime::FAILED_JOBS_TABLE,
    ];
}
