<?php

declare(strict_types=1);

use App\Http\Middleware\Teams\TeamsPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('allows the request when gate permits the middleware permission', function () {
    Gate::define('teams-middleware-probe-allow', fn () => true);

    $user = User::factory()->create();
    $this->actingAs($user);

    $middleware = app(TeamsPermission::class);
    $request = Request::create('/probe', 'GET');

    $response = $middleware->handle($request, fn () => response('ok', 200), 'teams-middleware-probe-allow');

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('ok');
});

it('aborts when gate denies the middleware permission', function () {
    Gate::define('teams-middleware-probe-deny', fn () => false);

    $user = User::factory()->create();
    $this->actingAs($user);

    $middleware = app(TeamsPermission::class);
    $request = Request::create('/probe', 'GET');

    expect(fn () => $middleware->handle($request, fn () => response('ok', 200), 'teams-middleware-probe-deny'))
        ->toThrow(HttpException::class);
});
