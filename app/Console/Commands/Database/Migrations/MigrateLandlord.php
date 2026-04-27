<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;

class MigrateLandlord extends BaseMigrationCommand
{
    protected $signature = 'migrate:landlord {--fresh} {--seed} {--force}';

    protected $description = 'Run migrations for the Landlord database';

    protected ConnectionType $connectionType = ConnectionType::LANDLORD;

    public function handle(): int
    {
        $dbName = databaseService()->generateLandlordDatabaseName();

        // Ensure DB exists (especially for fresh runs where it might have been dropped)
        databaseService()->createDatabase($dbName, ConnectionType::LANDLORD);

        $this->info('Running Landlord migrations...');
        $success = $this->executeMigrations($dbName);
        $this->info('Landlord migrations completed.');

        return $success ? 0 : 1;
    }
}
