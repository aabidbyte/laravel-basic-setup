<?php

declare(strict_types=1);

use App\Http\Middleware\Trash\EnableTrashedContext;
use App\Services\Trash\TrashedContext;
use Illuminate\Http\Request;

it('clears trashed context after the wrapped request completes', function () {
    $request = Request::create('http://localhost/trash/users', 'GET');
    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(static fn () => $route);

    $middleware = app(EnableTrashedContext::class);

    $activeDuringRequest = false;

    $middleware->handle($request, function (Request $req) use (&$activeDuringRequest) {
        $activeDuringRequest = TrashedContext::isActive();

        return response('ok', 200);
    });

    expect($activeDuringRequest)->toBeTrue()
        ->and(TrashedContext::isActive())->toBeFalse();
});
