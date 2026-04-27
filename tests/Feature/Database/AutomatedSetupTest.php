<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\Database\ConnectionType;
use App\Events\Database\MasterCreated;
use App\Events\Database\TenantCreated;
use App\Models\Master;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

/*
 * These tests require real DB commits so that DB::afterCommit callbacks fire.
 * Setting $connectionsToTransact = [] disables the DatabaseTransactions wrapping
 * for all connections while still using the standard TestCase base class.
 * Manual cleanup of inserted records is performed in afterEach.
 */

$landlordConn = ConnectionType::LANDLORD->connectionName();

beforeEach(function () {
    // Disable DatabaseTransactions for this test so DB::afterCommit fires
    $this->connectionsToTransact = [];

    // Ensure landlord tables exist
    $this->artisan('migrate:landlord');
});

afterEach(function () use ($landlordConn) {
    // Manual cleanup — order matters due to foreign keys
    DB::connection($landlordConn)->table('tenants')->delete();
    DB::connection($landlordConn)->table('masters')->delete();
    DB::connection($landlordConn)->table('users')->delete();
});

test('it dispatches master created event after commit', function () {
    Event::fake([MasterCreated::class]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $master = Master::create([
        'name' => 'Test Master',
        'db_name' => 'test_master_db',
    ]);

    Event::assertDispatched(MasterCreated::class, function ($event) use ($master) {
        return $event->master->id === $master->id;
    });

    expect($master->created_by_user_uuid)->toBe($user->uuid);
});

test('it dispatches tenant created event after commit', function () {
    Event::fake([TenantCreated::class]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $master = Master::create([
        'name' => 'Test Master',
        'db_name' => 'test_master_db',
    ]);

    $tenant = Tenant::create([
        'name' => 'Test Tenant',
        'db_name' => 'test_tenant_db',
        'master_id' => $master->id,
    ]);

    Event::assertDispatched(TenantCreated::class, function ($event) use ($tenant) {
        return $event->tenant->id === $tenant->id;
    });

    expect($tenant->created_by_user_uuid)->toBe($user->uuid);
});

test('it does not dispatch when using save quietly', function () {
    // Fake only our application events; leaving Eloquent model observers (HasUuid) intact
    Event::fake([MasterCreated::class, TenantCreated::class]);

    // Create the model using saveQuietly directly — this bypasses all observers
    $master = new Master([
        'name' => 'Quiet Master',
        'db_name' => 'quiet_master_db',
    ]);
    $master->uuid = (string) \Illuminate\Support\Str::uuid();
    $master->saveQuietly();

    Event::assertNotDispatched(MasterCreated::class);
});

test('setup listeners are queued', function () {
    Queue::fake();

    $master = Master::create([
        'name' => 'Queued Master',
        'db_name' => 'queued_master_db',
    ]);

    // ShouldQueue listeners are wrapped in CallQueuedListener, not pushed as themselves
    Queue::assertPushed(\Illuminate\Events\CallQueuedListener::class, function ($job) {
        return $job->class === \App\Listeners\Database\SetupMasterDatabase::class;
    });

    $tenant = Tenant::create([
        'name' => 'Queued Tenant',
        'db_name' => 'queued_tenant_db',
        'master_id' => $master->id,
    ]);

    Queue::assertPushed(\Illuminate\Events\CallQueuedListener::class, function ($job) {
        return $job->class === \App\Listeners\Database\SetupTenantDatabase::class;
    });
});
