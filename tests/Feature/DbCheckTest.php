<?php

use Illuminate\Support\Facades\DB;

test('it uses the correct database', function () {
    $dbName = DB::connection()->getDatabaseName();
    $centralDbName = DB::connection('central')->getDatabaseName();
    $tenantPrefix = config('tenancy.database.prefix');

    echo "Default DB: {$dbName}\n";
    echo "Central DB: {$centralDbName}\n";
    echo "Tenant DB prefix: {$tenantPrefix}\n";

    expect(\str_starts_with($dbName, 'laravel_testing'))->toBeTrue();
    expect($centralDbName)->toBe($dbName);
    expect($tenantPrefix)->toBe("testing_{$dbName}_tenant_");
});
