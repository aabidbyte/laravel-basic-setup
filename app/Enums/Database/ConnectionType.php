<?php

declare(strict_types=1);

namespace App\Enums\Database;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LandlordSeeder;
use Database\Seeders\MasterSeeder;
use Database\Seeders\TenantSeeder;

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
            self::LANDLORD => LandlordSeeder::class,
            self::MASTER => MasterSeeder::class,
            self::TENANT => TenantSeeder::class,
            default => DatabaseSeeder::class,
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
