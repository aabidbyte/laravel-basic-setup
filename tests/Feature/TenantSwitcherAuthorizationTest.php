<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    Permission::firstOrCreate(['name' => Permissions::IMPERSONATE_USERS()]);
    Permission::firstOrCreate(['name' => Permissions::VIEW_USERS()]);

    $suffix = Str::random(4);
    $this->tenantA = Tenant::factory()->create(['id' => 'ta' . $suffix]);
    $this->tenantB = Tenant::factory()->create(['id' => 'tb' . $suffix]);
    $this->tenantA->domains()->create(['domain' => "ta-{$suffix}.test"]);
    $this->tenantB->domains()->create(['domain' => "tb-{$suffix}.test"]);

    $this->victim = User::factory()->create(['name' => 'Victim User']);
    $this->victim->tenants()->attach([$this->tenantA->id, $this->tenantB->id]);

    $this->impersonator = User::factory()->create(['name' => 'Impersonator']);
    $this->impersonator->assignPermission(Permissions::IMPERSONATE_USERS());
    $this->impersonator->tenants()->attach($this->tenantA->id);

    $this->noImpersonateUser = User::factory()->create(['name' => 'No Impersonate']);
    $this->noImpersonateUser->assignPermission(Permissions::VIEW_USERS());
});

it('blocks tenant switcher selectTenant when the actor cannot impersonate users', function (): void {
    Livewire::actingAs($this->noImpersonateUser)
        ->test('tenancy.tenant-switcher')
        ->set('selectedUserUuid', $this->victim->uuid)
        ->call('selectTenant', $this->tenantA->id)
        ->assertDispatched('notify');

    $this->assertAuthenticatedAs($this->noImpersonateUser);
});

it('blocks selectTenant when the tenant is not one of the target users tenants', function (): void {
    Livewire::actingAs($this->impersonator)
        ->test('tenancy.tenant-switcher')
        ->set('selectedUserUuid', $this->victim->uuid)
        ->call('selectTenant', 'nonexistent-tenant-id')
        ->assertDispatched('notify');

    $this->assertAuthenticatedAs($this->impersonator);
});

it('blocks tenant impersonation when the actor is not a member of the selected tenant', function (): void {
    Livewire::actingAs($this->impersonator)
        ->test('tenancy.tenant-switcher')
        ->set('selectedUserUuid', $this->victim->uuid)
        ->call('selectTenant', $this->tenantB->id)
        ->assertDispatched('notify');

    $this->assertAuthenticatedAs($this->impersonator);
});
