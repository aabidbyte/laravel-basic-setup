<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    asTenant();
});

test('unauthenticated users are redirected from protected routes', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with([
    '/dashboard',
    '/settings/account',
    '/users',
    '/tenants',
    '/email-templates/contents',
    '/admin/errors',
]);

test('authenticated users can access unguarded admin routes', function (string $url) {
    $user = User::factory()->create();
    $user->roles()->detach();

    $this->actingAs($user)->get($url)->assertSuccessful();
})->with([
    '/admin/errors',
]);
