<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\info;

class NotificationsPruneReadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune-read {--days=30 : Number of days after read_at to prune}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune read notifications older than specified days (default: 30 days)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoffDate)
            ->delete();

        if ($deleted > 0) {
            info("Pruned {$deleted} read notifications older than {$days} days.");
        } else {
            info('No read notifications found to prune.');
        }

        return Command::SUCCESS;
    }
}
