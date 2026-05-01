<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\Database\DatabaseService;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Trait to manage multi-tenancy database isolation during tests.
 *
 * All workers (sequential or parallel) share the same databases.
 * Migrations and seeds run exactly once per suite via a file lock
 * and a checkpoint hash of all migration + seeder files.
 *
 * `DatabaseTransactions` wraps each test in a transaction for isolation.
 */
trait MultiTenancyTestCase
{
    protected string $testLandlordDb = 'test_landlord';

    protected static bool $migrated = false;

    protected function setupMultiTenancyTests(): void
    {
        $this->testLandlordDb = databaseService()->generateLandlordDatabaseName();

        DatabaseService::setLandlordDatabaseNameOverride($this->testLandlordDb);

        if (! self::$migrated) {
            $this->ensureDatabasesReady();
            self::$migrated = true;
        }
    }

    /**
     * Ensure shared test databases are migrated and seeded.
     * Uses a file lock to prevent parallel workers from racing,
     * and a checkpoint hash to skip migration when nothing changed.
     */
    protected function ensureDatabasesReady(): void
    {
        $baseDir = \storage_path('framework/testing');

        if (! \is_dir($baseDir)) {
            @\mkdir($baseDir, 0777, true);
        }

        $hashFile = $baseDir . '/db_ready.hash';
        $lockFile = $baseDir . '/db_ready.lock';
        $recreate = ! empty($_SERVER['LARAVEL_PARALLEL_TESTING_RECREATE_DATABASES']);
        $currentHash = $this->computeMigrationHash();

        // Fast path: hash matches → databases are current, skip migration
        if (! $recreate && \is_file($hashFile) && \file_get_contents($hashFile) === $currentHash) {
            return;
        }

        // Slow path: acquire lock and rebuild
        $fp = \fopen($lockFile, 'c+');

        if ($fp === false) {
            // Fallback: run migration without lock (single process mode)
            $this->refreshLandlordDatabase();

            return;
        }

        try {
            \flock($fp, LOCK_EX);

            // Double-check after acquiring lock (another worker may have finished)
            if (! $recreate && \is_file($hashFile) && \file_get_contents($hashFile) === $currentHash) {
                return;
            }

            $this->refreshLandlordDatabase();

            \file_put_contents($hashFile, $currentHash);
        } finally {
            \flock($fp, LOCK_UN);
            \fclose($fp);
        }
    }

    /**
     * Compute a hash of all migration and seeder files.
     * If any file changes, the hash changes, triggering a rebuild.
     */
    protected function computeMigrationHash(): string
    {
        $directories = [
            \base_path('database/migrations'),
            \base_path('database/seeders'),
        ];

        $hashes = [];

        foreach ($directories as $directory) {
            if (! \is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $hashes[] = \md5_file($file->getPathname());
                }
            }
        }

        \sort($hashes);

        return \md5(\implode('|', $hashes));
    }

    /**
     * Run migrations and seeds on all test databases.
     */
    protected function refreshLandlordDatabase(): void
    {
        $this->artisan('migrate:all', [
            '--fresh' => true,
            '--force' => true,
            '--seed' => true,
        ]);
    }
}
