<?php

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\CentralSeeders\Development\CentralUserSeeder;

it('seeds development users with stable UUIDs when model events are disabled', function (): void {
    $this->seed(CentralUserSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();
    $user = User::where('email', 'user@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin?->uuid)->not->toBeEmpty()
        ->and($user)->not->toBeNull()
        ->and($user?->uuid)->not->toBeEmpty();

    Tenant::query()->get()->each(function (Tenant $tenant): void {
        expect($tenant->database()->manager()->databaseExists($tenant->database()->getName()))->toBeTrue();
    });

    $adminUuid = $admin->uuid;
    $userUuid = $user->uuid;

    $this->seed(CentralUserSeeder::class);

    expect($admin->fresh()->uuid)->toBe($adminUuid)
        ->and($user->fresh()->uuid)->toBe($userUuid);
})->group('tenancy-provisioning');
