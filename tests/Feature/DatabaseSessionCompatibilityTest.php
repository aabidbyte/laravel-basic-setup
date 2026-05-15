<?php

use Illuminate\Support\Facades\DB;

test('database session driver can persist guest sessions without a uuid', function () {
    config([
        'session.driver' => 'database',
        'session.connection' => 'central',
    ]);

    DB::connection('central')->table('sessions')->delete();

    $this->get('http://laravel-basic-setup.test/login')->assertOk();

    $session = DB::connection('central')->table('sessions')->first();

    expect($session)->not->toBeNull()
        ->and($session->uuid)->toBeNull();
});
