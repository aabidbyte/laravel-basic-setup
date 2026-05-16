<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    asTenant();
});

test('unauthenticated users are redirected from protected routes', function (string $routeName) {
    $this->get(route($routeName, absolute: false))->assertRedirect(route('login'));
})->with([
    'dashboard',
    'settings.account',
    'users.index',
    'tenants.index',
    'emailTemplates.contents.index',
    'admin.errors.index',
]);

test('authenticated users can access unguarded admin routes', function (string $routeName) {
    $user = User::factory()->create();
    $user->roles()->detach();

    $this->actingAs($user)->get(route($routeName, absolute: false))->assertSuccessful();
})->with([
    'admin.errors.index',
]);
