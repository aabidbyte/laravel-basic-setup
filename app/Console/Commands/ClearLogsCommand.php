<?php

namespace App\Console\Commands;

use App\Constants\Logging\LogChannels;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class ClearLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear {--level= : Clear logs for a specific level only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all log files from storage/logs directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $logsPath = storage_path('logs');
        $level = $this->option('level');

        if ($level !== null) {
            return $this->clearLevelLogs($logsPath, $level);
        }

        return $this->clearAllLogs($logsPath);
    }

    /**
     * Clear all log files.
     */
    private function clearAllLogs(string $logsPath): int
    {
        $clearedCount = 0;

        // Clear main log files
        $mainLogs = ['laravel.log', 'browser.log'];
        foreach ($mainLogs as $logFile) {
            $logPath = "{$logsPath}/{$logFile}";
            if (File::exists($logPath)) {
                File::delete($logPath);
                $clearedCount++;
            }
        }

        // Clear level-specific log folders
        $levelChannels = LogChannels::levelChannels();
        $levelChannels[] = LogChannels::DEPRECATIONS;

        foreach ($levelChannels as $channel) {
            $channelPath = "{$logsPath}/{$channel}";
            if (File::isDirectory($channelPath)) {
                $files = File::glob("{$channelPath}/*.log");
                foreach ($files as $file) {
                    File::delete($file);
                    $clearedCount++;
                }
            }
        }

        // Clear Pail logs
        $pailPath = storage_path('pail');
        if (File::isDirectory($pailPath)) {
            $pailFiles = File::glob("{$pailPath}/*.pail");
            foreach ($pailFiles as $file) {
                File::delete($file);
                $clearedCount++;
            }
        }

        if ($clearedCount > 0) {
            info("Cleared {$clearedCount} log file(s).");
        } else {
            warning('No log files found to clear.');
        }

        return Command::SUCCESS;
    }

    /**
     * Clear logs for a specific level.
     */
    private function clearLevelLogs(string $logsPath, string $level): int
    {
        $validLevels = LogChannels::levelChannels();
        $validLevels[] = LogChannels::DEPRECATIONS;

        if (! in_array($level, $validLevels, true)) {
            warning("Invalid log level: {$level}. Valid levels are: " . implode(', ', $validLevels));

            return Command::FAILURE;
        }

        $clearedCount = 0;
        $levelPath = "{$logsPath}/{$level}";

        if (File::isDirectory($levelPath)) {
            $files = File::glob("{$levelPath}/*.log");
            foreach ($files as $file) {
                File::delete($file);
                $clearedCount++;
            }
        }

        if ($clearedCount > 0) {
            info("Cleared {$clearedCount} log file(s) for level: {$level}");
        } else {
            warning("No log files found for level: {$level}");
        }

        return Command::SUCCESS;
    }
}
