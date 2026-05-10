<?php

namespace Database\Seeders;

use Database\Seeders\CommonSeeders\RoleAndPermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            \Database\Seeders\CommonSeeders\EmailTemplateSeeder::class,
        ]);
    }
}
