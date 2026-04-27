<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Process\Pool;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Pool::command([
            PHP_BINARY,
            base_path('artisan'),
            'db:seed:landlord',
        ]);

        Pool::command([
            PHP_BINARY,
            base_path('artisan'),
            'db:seed:masters',
        ]);

        Pool::command([
            PHP_BINARY,
            base_path('artisan'),
            'db:seed:tenants',
        ]);
    }
}
