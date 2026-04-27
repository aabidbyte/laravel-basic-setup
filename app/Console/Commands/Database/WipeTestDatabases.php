<?php

declare(strict_types=1);

namespace App\Console\Commands\Database;

use App\Enums\Database\ConnectionType;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeTestDatabases extends Command
{
    protected $signature = 'db:wipe:test {--force : Force the operation to run}';

    protected $description = 'Wipe all test databases (test_landlord, test_master_*, test_tenant_*)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will wipe all test databases. Are you sure?')) {
            return 1;
        }

        $this->info('Wiping test databases...');

        $testLandlordDb = databaseService()->generateLandlordDatabaseName();
        $masterName = (string) config('app.name');
        $tenantNames = (array) config('tenancy.tenants', []);
        $dbsToWipe = [
            $testLandlordDb,
            databaseService()->generateTestingMasterDatabaseName($masterName),
        ];

        foreach ($tenantNames as $tenantName) {
            $dbsToWipe[] = databaseService()->generateTestingTenantDatabaseName($masterName, (string) $tenantName);
        }

        try {
            if (databaseExist($testLandlordDb, ConnectionType::LANDLORD)) {
                $this->info('Found test_landlord database. Reading registry...');
            } else {
                $this->info('test_landlord database not found. Nothing to wipe.');

                return 0;
            }

            $landlordConnection = configureDbConnection($testLandlordDb, ConnectionType::LANDLORD);
            $masters = [];
            $tenants = [];

            try {
                $masters = DB::connection($landlordConnection)->table('masters')->pluck('db_name')->all();
                $tenants = DB::connection($landlordConnection)->table('tenants')->pluck('db_name')->all();
            } catch (Exception $e) {
                $this->warn('Could not read registry tables (maybe migrations failed?): ' . $e->getMessage());
            }

            $dbsToWipe = \array_values(\array_unique(\array_merge($dbsToWipe, $masters, $tenants)));

            databaseService()->runAsRoot(ConnectionType::LANDLORD, function (string $connectionName) use ($dbsToWipe): void {
                $bar = $this->output->createProgressBar(\count($dbsToWipe));
                $bar->start();

                foreach ($dbsToWipe as $dbName) {
                    DB::connection($connectionName)->statement("DROP DATABASE IF EXISTS `{$dbName}`");
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            });

            databaseService()->purgeConnections([$landlordConnection]);
            $this->info('Test databases wiped successfully.');
        } catch (Exception $e) {
            $this->error('Failed to wipe databases: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }
}
