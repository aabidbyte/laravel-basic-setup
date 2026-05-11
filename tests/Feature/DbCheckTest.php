<?php

use Illuminate\Support\Facades\DB;

test('it uses the correct database', function () {
    $dbName = DB::connection()->getDatabaseName();
    $centralDbName = DB::connection('central')->getDatabaseName();
    
    echo "Default DB: {$dbName}\n";
    echo "Central DB: {$centralDbName}\n";
    
    expect($dbName)->toBe('laravel_testing');
    expect($centralDbName)->toBe('laravel_testing');
});
