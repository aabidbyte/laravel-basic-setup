<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Routes to appropriate seeder based on environment:
     * - Production: Essential data only (roles, permissions, teams, superAdmin)
     * - Development: All production data + sample teams and users
     */
    public function run(): void
    {
        if (isProduction()) {
            $this->call(ProductionSeeder::class);
        } else {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
