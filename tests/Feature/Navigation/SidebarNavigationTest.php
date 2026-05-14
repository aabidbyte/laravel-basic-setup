<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\User;
use App\Services\SideBarMenuService;

test('sidebar menu service can be instantiated', function () {
    $service = app(SideBarMenuService::class);

    expect($service)->toBeInstanceOf(SideBarMenuService::class);
});

test('authenticated user can access dashboard page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('sidebar contains platform section', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(__('navigation.platform'), false);
});

test('sidebar contains administration section', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(__('navigation.administration'), false);
});

test('unauthenticated user cannot see sidebar', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('sidebar includes email content link for users with email template permission', function () {
    $user = User::factory()->create();

    Permission::firstOrCreate(['name' => Permissions::VIEW_EMAIL_TEMPLATES()]);
    $user->assignPermission(Permissions::VIEW_EMAIL_TEMPLATES());

    $this->actingAs($user);

    $menus = app(SideBarMenuService::class)->getBottomMenus();
    $administrationMenu = collect($menus)->firstWhere('title', __('navigation.administration'));
    $emailTemplatesMenu = collect($administrationMenu['items'])->firstWhere('title', __('types.email_templates'));

    expect($emailTemplatesMenu)->not->toBeNull()
        ->and(collect($emailTemplatesMenu['items'])->contains(
            fn (array $item): bool => $item['title'] === __('types.email_contents')
                && $item['url'] === route('emailTemplates.contents.index'),
        ))->toBeTrue();
});

test('sidebar includes email layout link for users with email layout permission', function () {
    $user = User::factory()->create();

    Permission::firstOrCreate(['name' => Permissions::VIEW_EMAIL_LAYOUTS()]);
    $user->assignPermission(Permissions::VIEW_EMAIL_LAYOUTS());

    $this->actingAs($user);

    $menus = app(SideBarMenuService::class)->getBottomMenus();
    $administrationMenu = collect($menus)->firstWhere('title', __('navigation.administration'));
    $emailTemplatesMenu = collect($administrationMenu['items'])->firstWhere('title', __('types.email_templates'));

    expect($emailTemplatesMenu)->not->toBeNull()
        ->and(collect($emailTemplatesMenu['items'])->contains(
            fn (array $item): bool => $item['title'] === __('types.email_layouts')
                && $item['url'] === route('emailTemplates.layouts.index'),
        ))->toBeTrue();
});
