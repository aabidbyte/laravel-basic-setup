<?php

use App\Models\Master;
use Tests\Traits\UsesMasterDb;

uses(UsesMasterDb::class);

it('can connect to test master', function () {
    $token = databaseService()->getParallelTestingToken() ?: '1';
    $name = "test_{$token}";
    $master = Master::where('name', $name)->first();
    expect($master)->not->toBeNull();

    $expectedSlug = ucfirst(Str::camel($name));
    expect(DB::connection()->getDatabaseName())->toContain($expectedSlug);
});
