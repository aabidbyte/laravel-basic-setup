<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\Feature\FeatureKey;
use App\Models\CentralUser;
use App\Models\ErrorLog;
use App\Models\Feature;
use App\Models\Permission;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;
use App\Models\User;
use App\Services\ErrorHandling\Channels\DatabaseChannel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

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
    CentralUser::query()->create([
        'name' => 'Global User',
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    // 2. Create a tenant
    $id = 'isolation-test-' . Str::random(8);
    $tenant = Tenant::factory()->create(['id' => $id, 'should_seed' => false]);

    // 3. Initialize tenancy
    tenancy()->initialize($tenant);

    // 4. Verify user is still accessible
    expect(CentralUser::where('name', 'Global User')->exists())->toBeTrue();

    // 5. Verify the user is actually on the central connection
    $userFromDb = CentralUser::where('name', 'Global User')->first();
    expect($userFromDb->getConnectionName())->toBe('central');
});

test('tenant database error logs are mirrored to central error logs with tenant context', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'mirror-test-' . Str::random(8),
        'name' => 'Mirror Tenant',
        'should_seed' => false,
    ]);

    tenancy()->initialize($tenant);

    $channel = new DatabaseChannel();
    $channel->send(new Exception('Tenant mirrored error'), [
        'reference_id' => 'ERR-MIRROR-001',
        'exception_class' => Exception::class,
        'message' => 'Tenant mirrored error',
        'stack_trace' => 'Tenant trace',
        'url' => 'https://tenant.example.test/broken',
        'method' => 'GET',
        'tenant_id' => $tenant->getTenantKey(),
        'tenant_name' => $tenant->name,
        'tenant_domain' => 'mirror.example.test',
        'actor_type' => 'queue',
        'runtime_context' => 'queue',
        'request_data' => ['safe' => 'value'],
    ]);

    expect(ErrorLog::where('reference_id', 'ERR-MIRROR-001')->exists())->toBeTrue();

    $centralLog = ErrorLog::on('central')->where('reference_id', 'ERR-MIRROR-001')->first();

    expect($centralLog)->not->toBeNull()
        ->and($centralLog->tenant_id)->toBe($tenant->getTenantKey())
        ->and($centralLog->tenant_name)->toBe('Mirror Tenant')
        ->and($centralLog->tenant_domain)->toBe('mirror.example.test')
        ->and($centralLog->runtime_context)->toBe('queue')
        ->and($centralLog->context)->toBe(['safe' => 'value']);
});

test('tenant error log table only shows current tenant logs to permitted tenant users', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'logs-scope-' . Str::random(8),
        'name' => 'Logs Scope Tenant',
        'should_seed' => false,
    ]);

    tenancy()->initialize($tenant);

    Permission::firstOrCreate(['name' => Permissions::VIEW_ERROR_LOGS()]);

    $user = User::factory()->create();
    $user->assignPermission(Permissions::VIEW_ERROR_LOGS());

    ErrorLog::create([
        'reference_id' => 'ERR-TENANT-VISIBLE',
        'exception_class' => Exception::class,
        'message' => 'Visible tenant error',
        'stack_trace' => 'Trace',
        'tenant_id' => $tenant->getTenantKey(),
    ]);

    ErrorLog::create([
        'reference_id' => 'ERR-TENANT-HIDDEN',
        'exception_class' => Exception::class,
        'message' => 'Other tenant error',
        'stack_trace' => 'Trace',
        'tenant_id' => 'another-tenant',
    ]);

    Livewire::actingAs($user, 'tenant')
        ->test('tables.error-log-table')
        ->assertSee('Visible tenant error')
        ->assertDontSee('Other tenant error')
        ->assertDontSee(__('errors.management.all_tenants'));
});

test('tenant error log table can be enabled by tenant feature without user permission', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'logs-feature-' . Str::random(8),
        'name' => 'Logs Feature Tenant',
        'should_seed' => false,
    ]);

    $feature = Feature::query()->create([
        'key' => FeatureKey::ERROR_LOGS->value,
        'name' => FeatureKey::ERROR_LOGS->nameTranslations(),
        'type' => FeatureKey::ERROR_LOGS->valueType(),
        'default_value' => false,
        'is_active' => true,
    ]);

    TenantFeatureOverride::query()->create([
        'tenant_id' => $tenant->getTenantKey(),
        'feature_id' => $feature->id,
        'value' => true,
        'enabled' => true,
    ]);

    tenancy()->initialize($tenant);

    $user = User::factory()->create();

    ErrorLog::create([
        'reference_id' => 'ERR-TENANT-FEATURE',
        'exception_class' => Exception::class,
        'message' => 'Feature enabled tenant error',
        'stack_trace' => 'Trace',
        'tenant_id' => $tenant->getTenantKey(),
    ]);

    Livewire::actingAs($user, 'tenant')
        ->test('tables.error-log-table')
        ->assertSee('Feature enabled tenant error')
        ->assertDontSee(__('errors.management.all_tenants'));
});
