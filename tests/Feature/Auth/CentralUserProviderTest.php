<?php

declare(strict_types=1);

use App\Models\CentralUser;
use Database\Factories\UserFactory;

test('central user model reads from central users table', function () {
    $user = CentralUser::query()->forceCreate(UserFactory::new()->raw());

    $centralUser = CentralUser::query()->find($user->id);

    expect($centralUser)->toBeInstanceOf(CentralUser::class)
        ->and($centralUser->getTable())->toBe('users')
        ->and($centralUser->email)->toBe($user->email);
});

test('central user relationships use user id pivot keys', function () {
    $user = new CentralUser();

    expect($user->teams()->getForeignPivotKeyName())->toBe('user_id')
        ->and($user->roles()->getForeignPivotKeyName())->toBe('user_id')
        ->and($user->permissions()->getForeignPivotKeyName())->toBe('user_id')
        ->and($user->tenants()->getForeignPivotKeyName())->toBe('user_id');
});
