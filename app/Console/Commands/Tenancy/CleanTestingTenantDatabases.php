<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenancy;

use App\Services\Tenancy\TestingTenantDatabaseManager;
use Illuminate\Console\Command;

class CleanTestingTenantDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:clean-test-databases {--dry-run : List matching databases without dropping them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop tenant databases created by the MySQL test suite';

    /**
     * Execute the console command.
     */
    public function handle(TestingTenantDatabaseManager $databases): int
    {
        if (! \app()->environment('testing')) {
            $this->components->warn('This command only runs in the testing environment.');

            return self::FAILURE;
        }

        $matchingDatabases = $databases->allTestingTenantDatabases();

        if ($matchingDatabases->isEmpty()) {
            $this->components->info('No testing tenant databases found.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $matchingDatabases->each(function (string $database): void {
                $this->line($database);
            });

            return self::SUCCESS;
        }

        $droppedDatabases = $databases->dropAll();

        $this->components->info("Dropped {$droppedDatabases} testing tenant database(s).");

        return self::SUCCESS;
    }
}
