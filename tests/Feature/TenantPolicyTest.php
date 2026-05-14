<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\PolicyAbilities;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    foreach ([
        Permissions::VIEW_TENANTS(),
        Permissions::EDIT_TENANTS(),
        Permissions::DELETE_TENANTS(),
    ] as $name) {
        Permission::firstOrCreate(['name' => $name]);
    }

    $this->superAdminRole = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);
    $this->memberRole = Role::firstOrCreate(['name' => Roles::ADMIN]);

    $suffix = Str::random(4);
    $this->tenantOwned = Tenant::factory()->create(['id' => 'own' . $suffix]);
    $this->tenantOther = Tenant::factory()->create(['id' => 'oth' . $suffix]);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole($this->superAdminRole);

    $this->member = User::factory()->create();
    $this->member->assignRole($this->memberRole);
    $this->member->assignPermission(
        Permissions::VIEW_TENANTS(),
        Permissions::EDIT_TENANTS(),
        Permissions::DELETE_TENANTS(),
    );
    $this->member->tenants()->attach($this->tenantOwned);
});

it('allows super admins to view update and delete any tenant', function (): void {
    expect($this->superAdmin->can(PolicyAbilities::VIEW, $this->tenantOther))->toBeTrue()
        ->and($this->superAdmin->can(PolicyAbilities::UPDATE, $this->tenantOther))->toBeTrue()
        ->and($this->superAdmin->can(PolicyAbilities::DELETE, $this->tenantOther))->toBeTrue();
});

it('denies tenant view update and delete outside membership for non-super-admins', function (): void {
    expect($this->member->can(PolicyAbilities::VIEW, $this->tenantOwned))->toBeTrue()
        ->and($this->member->can(PolicyAbilities::VIEW, $this->tenantOther))->toBeFalse()
        ->and($this->member->can(PolicyAbilities::UPDATE, $this->tenantOther))->toBeFalse()
        ->and($this->member->can(PolicyAbilities::DELETE, $this->tenantOther))->toBeFalse();
});

it('returns forbidden when a member opens a tenant show page outside membership', function (): void {
    actingAs($this->member)
        ->get(route('tenants.show', $this->tenantOther->id))
        ->assertForbidden();
});
