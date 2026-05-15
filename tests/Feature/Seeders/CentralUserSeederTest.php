<?php

use App\Models\CentralUser;
use App\Models\Tenant;
use Database\Seeders\CentralSeeders\Development\CentralUserSeeder;
use Illuminate\Support\Facades\DB;

it('seeds development users with stable UUIDs when model events are disabled', function (): void {
    $this->seed(CentralUserSeeder::class);

    $admin = CentralUser::where('email', 'admin@example.com')->first();
    $user = CentralUser::where('email', 'user@example.com')->first();

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

it('seeds central users in central database when tenancy is initialized', function (): void {
    asTenant();

    $this->seed(CentralUserSeeder::class);

    expect(CentralUser::where('email', 'admin@example.com')->exists())->toBeTrue()
        ->and(DB::connection('tenant')->table('users')->where('email', 'admin@example.com')->exists())->toBeFalse();
})->group('tenancy-provisioning');
