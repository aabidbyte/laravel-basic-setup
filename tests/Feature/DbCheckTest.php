<?php

use Illuminate\Support\Facades\DB;

test('it uses the correct database', function () {
    $dbName = DB::connection()->getDatabaseName();
    $centralDbName = DB::connection('central')->getDatabaseName();
    $tenantPrefix = config('tenancy.database.prefix');

    expect(\str_starts_with($dbName, 'laravel_testing'))->toBeTrue();
    expect($centralDbName)->toBe($dbName);
    expect($tenantPrefix)->toStartWith('testing_');
    expect($tenantPrefix)->toEndWith('_tenant_');
    expect(\strlen($tenantPrefix))->toBeLessThanOrEqual(64);
});
