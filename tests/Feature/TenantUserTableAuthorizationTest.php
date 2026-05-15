<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Livewire\Tables\TenantUserAssignmentTable;
use App\Livewire\Tables\UserTable;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    DB::connection('central')->statement('SET FOREIGN_KEY_CHECKS=0');

    Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);
    Permission::firstOrCreate(['name' => Permissions::EDIT_TENANTS()]);
    Permission::firstOrCreate(['name' => Permissions::IMPERSONATE_USERS()]);
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);

    $suffix = Str::random(4);
    $this->tenant = Tenant::factory()->create(['id' => 'tu' . $suffix]);

    $this->viewerOnly = User::factory()->create();
    $this->viewerOnly->assignPermission(Permissions::VIEW_USERS());

    $this->member = User::factory()->create();
    $this->member->assignPermission(Permissions::VIEW_USERS(), Permissions::EDIT_TENANTS());
    $this->member->tenants()->attach($this->tenant);

    $this->tenantUser = User::factory()->create();
    $this->tenant->users()->attach($this->tenantUser->id);
});

afterEach(function (): void {
    DB::connection('central')->statement('SET FOREIGN_KEY_CHECKS=1');
});

it('does not detach when executeAction is invoked without edit tenants permission', function (): void {
    $pivotCount = DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('user_id', $this->tenantUser->id)
        ->count();

    expect($pivotCount)->toBe(1);

    Livewire::actingAs($this->viewerOnly)
        ->test('tables.tenant-user-table', ['tenantId' => $this->tenant->tenant_id])
        ->call('executeAction', 'detach', $this->tenantUser->uuid);

    $after = DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('user_id', $this->tenantUser->id)
        ->count();

    expect($after)->toBe(1);
});

it('detaches when executeAction is invoked by an authorized tenant editor', function (): void {
    Livewire::actingAs($this->member)
        ->test('tables.tenant-user-table', ['tenantId' => $this->tenant->tenant_id])
        ->call('executeAction', 'detach', $this->tenantUser->uuid);

    $after = DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('user_id', $this->tenantUser->id)
        ->count();

    expect($after)->toBe(0);
});

it('assigns and removes users from the split tenant user tables', function (): void {
    $otherTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $unassignedUser = User::factory()->create();

    $this->member->tenants()->attach($otherTenant);
    $otherTenant->users()->attach($unassignedUser);

    $assignmentEvent = "tenant-user-assignments-updated.{$this->tenant->tenant_id}";
    $assignedTable = Livewire::actingAs($this->member)
        ->test('tables.tenant-user-assignment-table', ['tenantId' => $this->tenant->tenant_id])
        ->set('search', $unassignedUser->email)
        ->assertDontSee($unassignedUser->email);

    Livewire::actingAs($this->member)
        ->test('tables.tenant-assignable-user-table', ['tenantId' => $this->tenant->tenant_id])
        ->set('search', $unassignedUser->email)
        ->call('handleRowClick', $unassignedUser->uuid)
        ->assertDispatched($assignmentEvent);

    expect(DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('user_id', $unassignedUser->id)
        ->exists())->toBeTrue();

    $assignedTable
        ->dispatch($assignmentEvent)
        ->assertSee($unassignedUser->email);

    $assignedTable = Livewire::actingAs($this->member)
        ->test('tables.tenant-user-assignment-table', ['tenantId' => $this->tenant->tenant_id])
        ->set('search', $unassignedUser->email)
        ->call('handleRowClick', $unassignedUser->uuid)
        ->assertDispatched($assignmentEvent);

    expect(DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->tenant_id)
        ->where('user_id', $unassignedUser->id)
        ->exists())->toBeFalse();

    Livewire::actingAs($this->member)
        ->test('tables.tenant-assignable-user-table', ['tenantId' => $this->tenant->tenant_id])
        ->set('search', $unassignedUser->email)
        ->dispatch($assignmentEvent)
        ->assertSee($unassignedUser->email);
});

it('limits assignable users for tenant editors to users from their tenants', function (): void {
    $otherTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $sharedTenantUser = User::factory()->create();
    $otherTenantUser = User::factory()->create();

    $this->member->tenants()->attach($otherTenant);
    $otherTenant->users()->attach($sharedTenantUser);

    $isolatedTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $isolatedTenant->users()->attach($otherTenantUser);

    Livewire::actingAs($this->member)
        ->test('tables.tenant-assignable-user-table', ['tenantId' => $this->tenant->tenant_id])
        ->set('search', $sharedTenantUser->email)
        ->assertSee($sharedTenantUser->email)
        ->assertDontSee($otherTenantUser->email);
});

it('lets super admins filter assignable users by tenant across all tenants', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $superAdmin->assignPermission(Permissions::VIEW_USERS(), Permissions::EDIT_TENANTS());

    $firstTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $secondTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $centralUser = User::factory()->create();
    $firstTenantUser = User::factory()->create();
    $secondTenantUser = User::factory()->create();

    $firstTenant->users()->attach($firstTenantUser);
    $secondTenant->users()->attach($secondTenantUser);

    Livewire::actingAs($superAdmin)
        ->test('tables.tenant-assignable-user-table', ['tenantId' => $this->tenant->tenant_id])
        ->set('filters.tenant_id', $secondTenant->tenant_id)
        ->assertSee($secondTenantUser->email)
        ->assertDontSee($firstTenantUser->email)
        ->assertDontSee($centralUser->email);

    Livewire::actingAs($superAdmin)
        ->test('tables.tenant-assignable-user-table', ['tenantId' => $this->tenant->tenant_id])
        ->set('filters.tenant_id', TenantUserAssignmentTable::CENTRAL_USERS_FILTER)
        ->set('search', $centralUser->email)
        ->assertSee($centralUser->email)
        ->assertDontSee($firstTenantUser->email)
        ->assertDontSee($secondTenantUser->email);
});

it('limits the main users table to tenant members visible to tenant editors', function (): void {
    $otherTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $visibleUser = User::factory()->create();
    $otherTenantUser = User::factory()->create();
    $centralUser = User::factory()->create();

    $this->tenant->users()->attach($visibleUser);
    $otherTenant->users()->attach($otherTenantUser);

    Livewire::actingAs($this->member)
        ->test('tables.user-table')
        ->set('search', $visibleUser->email)
        ->assertSee($visibleUser->email);

    Livewire::actingAs($this->member)
        ->test('tables.user-table')
        ->set('search', $otherTenantUser->email)
        ->assertDontSee($otherTenantUser->email);

    Livewire::actingAs($this->member)
        ->test('tables.user-table')
        ->set('search', $centralUser->email)
        ->assertDontSee($centralUser->email);
});

it('lets super admins browse all tenant users and explicitly filter central users', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $superAdmin->assignPermission(Permissions::VIEW_USERS());

    $firstTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $secondTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $firstTenantUser = User::factory()->create();
    $secondTenantUser = User::factory()->create();
    $centralUser = User::factory()->create();

    $firstTenant->users()->attach($firstTenantUser);
    $secondTenant->users()->attach($secondTenantUser);

    Livewire::actingAs($superAdmin)
        ->test('tables.user-table')
        ->set('search', $firstTenantUser->email)
        ->assertSee($firstTenantUser->email);

    Livewire::actingAs($superAdmin)
        ->test('tables.user-table')
        ->set('search', $secondTenantUser->email)
        ->assertSee($secondTenantUser->email);

    Livewire::actingAs($superAdmin)
        ->test('tables.user-table')
        ->set('search', $centralUser->email)
        ->assertDontSee($centralUser->email);

    Livewire::actingAs($superAdmin)
        ->test('tables.user-table')
        ->set('search', $superAdmin->email)
        ->assertDontSee($superAdmin->email);

    Livewire::actingAs($superAdmin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER)
        ->set('search', $centralUser->email)
        ->assertSee($centralUser->email)
        ->assertDontSee($firstTenantUser->email)
        ->assertDontSee($secondTenantUser->email);

    Livewire::actingAs($superAdmin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', UserTable::CENTRAL_USERS_FILTER)
        ->set('search', $superAdmin->email)
        ->assertSee($superAdmin->email)
        ->assertSee(__('tenancy.central_users'));

    Livewire::actingAs($superAdmin)
        ->test('tables.user-table')
        ->set('filters.tenant_id', $secondTenant->tenant_id)
        ->set('search', $secondTenantUser->email)
        ->assertSee($secondTenantUser->email)
        ->assertDontSee($firstTenantUser->email)
        ->assertDontSee($centralUser->email);
});

it('sends tenant uuids to the impersonation tenant picker', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $superAdmin->assignPermission(Permissions::VIEW_USERS(), Permissions::IMPERSONATE_USERS());

    $otherTenant = Tenant::factory()->create(['id' => 'tu' . Str::random(4)]);
    $targetUser = User::factory()->create();
    $targetUser->tenants()->attach([$this->tenant->tenant_id, $otherTenant->tenant_id]);

    Livewire::actingAs($superAdmin)
        ->test('tables.impersonate-user-table')
        ->call('initiateImpersonation', $targetUser->uuid)
        ->assertDispatched('prompt-tenant-selection', function (string $event, array $params) use ($otherTenant): bool {
            $tenantIds = collect($params[0]['tenants'] ?? [])->pluck('id');

            return $tenantIds->contains($this->tenant->tenant_id)
                && $tenantIds->contains($otherTenant->tenant_id)
                && ! $tenantIds->contains($this->tenant->id);
        });
});
