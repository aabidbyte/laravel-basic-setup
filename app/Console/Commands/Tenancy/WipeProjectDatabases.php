<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenancy;

use App\Services\Tenancy\ProjectDatabaseWiper;
use Illuminate\Console\Command;

class WipeProjectDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:wipe-project
        {--dry-run : List matching databases without dropping them}
        {--force : Drop databases without asking for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop the central and tenant databases created by this project in development or staging';

    /**
     * Execute the console command.
     */
    public function handle(ProjectDatabaseWiper $databases): int
    {
        if (! $this->canRunInCurrentEnvironment()) {
            $this->components->error('This command only runs in local, development, dev, or staging environments.');

            return self::FAILURE;
        }

        $matchingDatabases = $databases->projectDatabases();

        if ($matchingDatabases->isEmpty()) {
            $this->components->info('No project databases found.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $matchingDatabases->each(function (string $database): void {
                $this->line($database);
            });

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirmWipe($matchingDatabases->all())) {
            $this->components->warn('Database wipe cancelled.');

            return self::FAILURE;
        }

        $droppedDatabases = $databases->dropAll();

        $this->components->info("Dropped {$droppedDatabases} project database(s).");

        return self::SUCCESS;
    }

    private function canRunInCurrentEnvironment(): bool
    {
        return \app()->environment(['local', 'development', 'dev', 'staging']);
    }

    /**
     * @param  array<int, string>  $databases
     */
    private function confirmWipe(array $databases): bool
    {
        $this->components->warn('This will permanently drop these databases:');

        foreach ($databases as $database) {
            $this->line($database);
        }

        return $this->confirm('Drop these project databases?', false);
    }
}
