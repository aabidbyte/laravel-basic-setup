<?php

declare(strict_types=1);

use App\Models\ErrorLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Test that data created in one tenant is not visible in another.
 */
test('tenant data isolation', function () {
    // 1. Create two tenants with unique IDs
    $id1 = 'tenant-' . Str::random(8);
    $id2 = 'tenant-' . Str::random(8);

    $tenant1 = Tenant::factory()->create(['id' => $id1, 'should_seed' => false]);
    $tenant2 = Tenant::factory()->create(['id' => $id2, 'should_seed' => false]);

    // 2. Initialize Tenant 1 and create an error log
    tenancy()->initialize($tenant1);

    $error1 = ErrorLog::create([
        'reference_id' => 'ERR-1',
        'exception_class' => 'Exception',
        'message' => 'Error in Tenant 1',
        'stack_trace' => '...',
    ]);

    expect(tenant()->getTenantKey())->toBe($id1)
        ->and(ErrorLog::where('reference_id', 'ERR-1')->exists())->toBeTrue()
        ->and(ErrorLog::all())->toHaveCount(1);

    // 3. Switch to Tenant 2
    tenancy()->initialize($tenant2);

    expect(tenant()->getTenantKey())->toBe($id2);

    // 4. Verify Error 1 is NOT visible in Tenant 2
    expect(ErrorLog::where('reference_id', 'ERR-1')->exists())->toBeFalse()
        ->and(ErrorLog::all())->toHaveCount(0);

    // 5. Create an error log in Tenant 2
    ErrorLog::create([
        'reference_id' => 'ERR-2',
        'exception_class' => 'Exception',
        'message' => 'Error in Tenant 2',
        'stack_trace' => '...',
    ]);

    expect(ErrorLog::where('reference_id', 'ERR-2')->exists())->toBeTrue()
        ->and(ErrorLog::all())->toHaveCount(1);

    // 6. Switch back to Tenant 1
    tenancy()->initialize($tenant1);

    // 7. Verify only Tenant 1's error is visible
    expect(ErrorLog::where('reference_id', 'ERR-1')->exists())->toBeTrue()
        ->and(ErrorLog::where('reference_id', 'ERR-2')->exists())->toBeFalse()
        ->and(ErrorLog::all())->toHaveCount(1);
});

/**
 * Test that global models (like Users) are accessible across tenants but remain in central DB.
 */
test('central model access from tenant context', function () {
    // 1. Create a user (central)
    $user = User::factory()->create(['name' => 'Global User']);

    // 2. Create a tenant
    $id = 'isolation-test-' . Str::random(8);
    $tenant = Tenant::factory()->create(['id' => $id, 'should_seed' => false]);

    // 3. Initialize tenancy
    tenancy()->initialize($tenant);

    // 4. Verify user is still accessible
    expect(User::where('name', 'Global User')->exists())->toBeTrue();

    // 5. Verify the user is actually on the central connection
    $userFromDb = User::where('name', 'Global User')->first();
    expect($userFromDb->getConnectionName())->toBe('central');
});
