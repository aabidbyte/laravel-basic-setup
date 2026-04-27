<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;

class SeedLandlord extends BaseSeederCommand
{
    protected $signature = 'db:seed:landlord {--force : Force the operation to run when in production}';

    protected $description = 'Run seeders for the Landlord database';

    protected ConnectionType $connectionType = ConnectionType::LANDLORD;

    public function handle(): void
    {
        $this->info('Seeding Landlord database...');

        $dbName = databaseService()->generateLandlordDatabaseName();
        $connectionName = databaseService()->createDynamicConnection($dbName, ConnectionType::LANDLORD);

        $this->call('db:seed', [
            '--class' => ConnectionType::LANDLORD->seederClass(),
            '--force' => $this->option('force'),
            '--database' => $connectionName,
        ]);

        $this->info('Landlord database seeding completed.');
    }
}
