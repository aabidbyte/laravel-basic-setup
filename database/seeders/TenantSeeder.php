<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Common Seeders
        // $this->call(\Database\Seeders\Tenants\CommonSeeders\Production\StoreConfigSeeder::class);

        if (! isProduction()) {
            // Development seeders
            // $this->call(\Database\Seeders\Tenants\CommonSeeders\Development\TestOrdersSeeder::class);
        }
    }
}
