<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

class SeedAll extends BaseSeederCommand
{
    protected $signature = 'db:seed:all {--force : Force the operation to run when in production}';

    protected $description = 'Run all seeders (Landlord -> Masters -> Tenants)';

    public function handle(): void
    {
        $this->info('Starting full seeding sequence...');

        $this->info('1/3 Landlord Seeders');
        $this->call('db:seed:landlord');

        $this->info('2/3 Master Seeders');
        $this->call('db:seed:masters');

        $this->info('3/3 Tenants Seeders');
        $this->call('db:seed:tenants');

        $this->info('Full seeding sequence completed successfully!');
    }
}
