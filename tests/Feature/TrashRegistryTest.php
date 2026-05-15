<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Enums\Trash\TrashEntityType;
use App\Models\Permission;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SideBarMenuService;
use App\Services\Trash\TrashRegistry;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

test('trash registry exposes all managed trash entities', function (): void {
    $registry = app(TrashRegistry::class);

    expect($registry->getEntityTypes())->toEqual([
        'users',
        'roles',
        'teams',
        'error-logs',
        'plans',
        'features',
        'subscriptions',
        'tenants',
        'email-templates',
    ])
        ->and(TrashEntityType::values())->toEqual($registry->getEntityTypes())
        ->and($registry->getRoutePattern())->toBe('users|roles|teams|error-logs|plans|features|subscriptions|tenants|email-templates');
});

test('tenant trash configuration uses tenant id as the route key', function (): void {
    $tenantConfig = app(TrashRegistry::class)->getEntity('tenants');

    expect($tenantConfig)
        ->not->toBeNull()
        ->and($tenantConfig['model'])->toBe(Tenant::class)
        ->and($tenantConfig['routeKey'])->toBe('tenant_id')
        ->and(\in_array(SoftDeletes::class, class_uses_recursive(Tenant::class), true))->toBeTrue()
        ->and(Schema::hasColumn('tenants', 'deleted_at'))->toBeTrue();
});

test('trash sidebar includes newly registered entities when authorized', function (): void {
    $user = User::factory()->create();

    foreach ([
        Permissions::VIEW_PLANS(),
        Permissions::VIEW_FEATURES(),
        Permissions::VIEW_SUBSCRIPTIONS(),
        Permissions::VIEW_TENANTS(),
        Permissions::VIEW_EMAIL_TEMPLATES(),
    ] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
        $user->assignPermission($permission);
    }

    $this->actingAs($user);

    $menus = app(SideBarMenuService::class)->getBottomMenus();
    $administrationMenu = collect($menus)->firstWhere('title', __('navigation.administration'));
    $trashMenu = collect($administrationMenu['items'])->firstWhere('title', __('navigation.trashed'));
    $trashUrls = collect($trashMenu['items'])->pluck('url')->all();

    expect($trashUrls)->toContain(
        route('trash.index', ['entityType' => 'plans']),
        route('trash.index', ['entityType' => 'features']),
        route('trash.index', ['entityType' => 'subscriptions']),
        route('trash.index', ['entityType' => 'tenants']),
        route('trash.index', ['entityType' => 'email-templates']),
    );
});
