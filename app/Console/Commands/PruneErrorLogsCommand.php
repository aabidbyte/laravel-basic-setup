<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ErrorLog;
use Illuminate\Console\Command;

/**
 * Command to prune old error logs.
 *
 * Removes error logs older than the configured retention period.
 * Can be scheduled to run daily to keep the error_logs table manageable.
 */
class PruneErrorLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'errors:prune
                            {--days= : Number of days to retain (default from config)}
                            {--resolved : Only prune resolved errors}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old error logs from the database';

    /**
     * Execute the console command.
     *
     * @return int Command exit code
     */
    public function handle(): int
    {
        $days = $this->option('days')
            ?? config('error-handling.channels.database.retention_days', 30);

        $days = (int) $days;

        $query = ErrorLog::where('created_at', '<', now()->subDays($days));

        if ($this->option('resolved')) {
            $query->whereNotNull('resolved_at');
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No error logs to prune.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Would delete {$count} error log(s) older than {$days} days.");

            return self::SUCCESS;
        }

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        $query->chunkById(100, function ($logs) use (&$deleted) {
            foreach ($logs as $log) {
                $log->forceDelete(); // Bypass soft delete for cleanup
                $deleted++;
            }
        });

        $this->info("Deleted {$deleted} error log(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
