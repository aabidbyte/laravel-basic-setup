<?php

declare(strict_types=1);

namespace App\Enums\Database;

/**
 * multi-tenancy database connection tiers
 */
enum ConnectionType: string
{
    case LANDLORD = 'landlord';
    case MASTER = 'master';
    case TENANT = 'tenant';
    case TESTS = 'tests';

    public function connectionName(): string
    {
        return match ($this) {
            self::LANDLORD => 'landlord',
            self::MASTER => 'master',
            self::TENANT => 'tenant',
            self::TESTS => 'tests',
        };
    }

    public function migrationPath(): string
    {
        return match ($this) {
            self::LANDLORD => 'database/migrations/LandlordMigrations',
            self::MASTER => 'database/migrations/Masters',
            self::TENANT => 'database/migrations/Tenants',
        };
    }

    public function seederPath(): string
    {
        return match ($this) {
            self::LANDLORD => 'database/seeders/LandlordSeeders',
            self::MASTER => 'database/seeders/Masters',
            self::TENANT => 'database/seeders/Tenants',
        };
    }

    public function seederClass(): string
    {
        return match ($this) {
            self::LANDLORD => \Database\Seeders\LandlordSeeder::class,
            self::MASTER => \Database\Seeders\MasterSeeder::class,
            self::TENANT => \Database\Seeders\TenantSeeder::class,
            default => \Database\Seeders\DatabaseSeeder::class,
        };
    }

    public function tag(): string
    {
        return match ($this) {
            self::LANDLORD => 'landlord',
            self::MASTER => 'master',
            self::TENANT => 'tenant',
            self::TESTS => 'tests',
        };
    }
}
