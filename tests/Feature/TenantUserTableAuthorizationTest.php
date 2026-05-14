<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);
    Permission::firstOrCreate(['name' => Permissions::EDIT_TENANTS()]);

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

it('does not detach when executeAction is invoked without edit tenants permission', function (): void {
    $pivotCount = DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->id)
        ->where('user_id', $this->tenantUser->id)
        ->count();

    expect($pivotCount)->toBe(1);

    Livewire::actingAs($this->viewerOnly)
        ->test('tables.tenant-user-table', ['tenantId' => $this->tenant->id])
        ->call('executeAction', 'detach', $this->tenantUser->uuid);

    $after = DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->id)
        ->where('user_id', $this->tenantUser->id)
        ->count();

    expect($after)->toBe(1);
});

it('detaches when executeAction is invoked by an authorized tenant editor', function (): void {
    Livewire::actingAs($this->member)
        ->test('tables.tenant-user-table', ['tenantId' => $this->tenant->id])
        ->call('executeAction', 'detach', $this->tenantUser->uuid);

    $after = DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->id)
        ->where('user_id', $this->tenantUser->id)
        ->count();

    expect($after)->toBe(0);
});

it('assigns and detaches users from the tenant assignment table', function (): void {
    $unassignedUser = User::factory()->create();

    Livewire::actingAs($this->member)
        ->test('tables.tenant-user-assignment-table', ['tenantId' => $this->tenant->id])
        ->call('executeAction', 'select', $unassignedUser->uuid);

    expect(DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->id)
        ->where('user_id', $unassignedUser->id)
        ->exists())->toBeTrue();

    Livewire::actingAs($this->member)
        ->test('tables.tenant-user-assignment-table', ['tenantId' => $this->tenant->id])
        ->set('selected', [$unassignedUser->uuid])
        ->call('executeBulkAction', 'detach');

    expect(DB::connection('central')->table('tenant_user')
        ->where('tenant_id', $this->tenant->id)
        ->where('user_id', $unassignedUser->id)
        ->exists())->toBeFalse();
});
