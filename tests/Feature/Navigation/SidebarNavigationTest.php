<?php

declare(strict_types=1);

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
        ->assertSee(__('Platform'), false);
});

test('sidebar contains resources section', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(__('Resources'), false);
});

test('unauthenticated user cannot see sidebar', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});
