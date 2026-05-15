<?php

use App\Constants\Auth\Roles;
use App\Models\CentralUser;
use Database\Seeders\CentralSeeders\Production\RoleAndPermissionSeeder;
use Database\Seeders\CentralSeeders\Production\SuperAdminSeeder;
use Illuminate\Support\Facades\DB;

it('seeds configured super admins once in the central database', function (): void {
    config([
        'seeder.super_admin_emails' => 'Admin@Example.com, admin@example.com, owner@example.com',
        'seeder.super_admin_password' => 'configured-password',
    ]);

    asTenant();

    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $admins = CentralUser::whereIn('email', ['admin@example.com', 'owner@example.com'])->get();

    expect($admins)->toHaveCount(2)
        ->and(CentralUser::where('email', 'admin@example.com')->count())->toBe(1)
        ->and(CentralUser::where('email', 'owner@example.com')->count())->toBe(1)
        ->and(DB::connection('tenant')->table('users')->whereIn('email', ['admin@example.com', 'owner@example.com'])->exists())->toBeFalse();
});

it('assigns the super admin role to configured super admins', function (): void {
    config([
        'seeder.super_admin_emails' => 'security@example.com',
        'seeder.super_admin_password' => 'configured-password',
    ]);

    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $user = CentralUser::where('email', 'security@example.com')->firstOrFail();

    expect($user->roles()->where('name', Roles::SUPER_ADMIN)->exists())->toBeTrue()
        ->and($user->is_super_admin)->toBeTrue();
});
