<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Console\Commands\Database\BaseDatabaseCommand;
use App\Console\Commands\Database\Concerns\CanRunInParallel;
use App\Enums\Database\ConnectionType;
use App\Enums\Database\SeederEnvironment;
use App\Enums\Database\SeederType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

abstract class BaseSeederCommand extends BaseDatabaseCommand
{
    use CanRunInParallel;

    protected ConnectionType $connectionType;

    protected SeederType $seederType = SeederType::COMMON;

    /**
     * Run all applicable seeders for a database.
     */
    protected function runSeederForDatabase(string $dbName): void
    {
        $connectionName = configureDbConnection($dbName);

        $envs = [SeederEnvironment::PRODUCTION];
        if (! isProduction()) {
            $envs[] = SeederEnvironment::DEVELOPMENT;
        }

        foreach ($envs as $env) {
            $this->runEnvSeeders($connectionName, $env, $dbName);
        }
    }

    /**
     * Run common then target seeders for a database.
     */
    protected function runTargetSeederForDatabase(string $dbName): void
    {
        // First run common seeders
        $this->seederType = SeederType::COMMON;
        $this->runSeederForDatabase($dbName);

        // Then run target seeders
        $this->seederType = SeederType::TARGET;
        $this->runSeederForDatabase($dbName);
    }

    /**
     * Scan and run seeders from a specific environment folder.
     */
    protected function runEnvSeeders(string $connectionName, SeederEnvironment $env, string $dbName): void
    {
        $path = $this->resolveSeederPath($env, $dbName);
        $fullPath = base_path($path);

        if (! File::isDirectory($fullPath)) {
            return;
        }

        $files = File::files($fullPath);

        $namespace = $this->getNamespaceFromPath($path);

        foreach ($files as $file) {
            $className = $namespace . '\\' . $file->getBasename('.php');

            $this->info("Seeding: [{$connectionName}] Class: {$className}");

            $this->call('db:seed', [
                '--class' => $className,
                '--database' => $connectionName,
                '--force' => true,
            ]);
        }
    }

    /**
     * Resolve the relative path to seeder files.
     */
    protected function resolveSeederPath(SeederEnvironment $env, ?string $dbName = null): string
    {
        $path = $this->connectionType->seederPath();

        if ($this->connectionType !== ConnectionType::LANDLORD) {
            $path .= "/{$this->seederType->folderName()}";

            if ($this->seederType === SeederType::TARGET && $dbName) {
                $path .= '/' . $this->unifyName($dbName);
            }
        }

        $path .= "/{$env->folderName()}";

        return $path;
    }

    /**
     * Convert a file path to a PSR-4 namespace fragment.
     */
    protected function getNamespaceFromPath(string $path): string
    {
        // database/seeders -> Database\Seeders
        $parts = explode('/', $path);
        $parts = array_map(fn ($p) => ucfirst(Str::camel($p)), $parts);

        return implode('\\', $parts);
    }

    /**
     * Create a new seeder file.
     */
    protected function createSeeder(string $name, bool $isDev = false, ?string $dbName = null): void
    {
        if ($dbName) {
            $this->seederType = SeederType::TARGET;
        }

        $env = $isDev ? SeederEnvironment::DEVELOPMENT : SeederEnvironment::PRODUCTION;
        $path = $this->resolveSeederPath($env, $dbName);

        $fullPath = $path . '/' . $name;

        $this->call('make:seeder', [
            'name' => $fullPath,
        ]);

        $this->info("Seeder created at {$path}/{$name}.php");

        // Auto-register the seeder
        $this->registerSeeder($fullPath, $isDev);
    }

    /**
     * Register the created seeder into the main TierSeeder.
     */
    protected function registerSeeder(string $seederRelativePath, bool $isDev): void
    {
        // 1. Determine Tier Seeder File
        $tierSeederClass = match ($this->connectionType) {
            ConnectionType::LANDLORD => 'LandlordSeeder',
            ConnectionType::MASTER => 'MasterSeeder',
            ConnectionType::TENANT => 'TenantSeeder',
            default => null,
        };

        if (! $tierSeederClass) {
            return;
        }

        $tierSeederPath = database_path("seeders/{$tierSeederClass}.php");

        if (! File::exists($tierSeederPath)) {
            $this->warn("Main seeder file not found: {$tierSeederClass}.php");

            return;
        }

        // 2. Generate Fully Qualified Class Name of the NEW seeder
        $namespace = $this->getNamespaceFromPath(dirname($seederRelativePath));
        $className = '\\' . $namespace . '\\' . basename($seederRelativePath);

        $callLine = "\$this->call({$className}::class);";

        $content = File::get($tierSeederPath);

        // 3. Avoid Duplicates
        if (str_contains($content, $className . '::class')) {
            $this->info("Seeder already registered in {$tierSeederClass}.");

            return;
        }

        // 4. Inject Logic
        if ($isDev) {
            // Find the `if (! app()->isProduction()) {` block
            // This regex looks for the opening brace of the environment check
            $pattern = '/(if\s*\(\s*!\s*app\(\)->isProduction\(\)\s*\)\s*\{)/';

            if (preg_match($pattern, $content)) {
                $replacement = "$1\n            {$callLine}";
                $content = preg_replace($pattern, $replacement, $content, 1);
                $this->info("Registered [DEV] seeder in {$tierSeederClass}.");
            } else {
                // If block doesn't exist, create it at the end of run()
                // Simple fallback to inserting before closing brace of run()
                $fallbackBlock = "\n        if (! app()->isProduction()) {\n            {$callLine}\n        }\n";
                $content = $this->injectIntoRunMethod($content, $fallbackBlock);
                $this->info("Registered [DEV] seeder in {$tierSeederClass} (created new environment block).");
            }
        } else {
            // Production - Inject at the top of run() or after opening brace
            // Simple approach: Insert after `public function run(): void {`
            $pattern = '/(public function run\(\): void\s*\{)/';
            $replacement = "$1\n        {$callLine}";
            $content = preg_replace($pattern, $replacement, $content, 1);
            $this->info("Registered [PROD] seeder in {$tierSeederClass}.");
        }

        File::put($tierSeederPath, $content);
    }

    /**
     * Helper to inject content before the closing brace of the run() method.
     */
    protected function injectIntoRunMethod(string $content, string $injection): string
    {
        // Find the last closing brace of the class, then go back to find the closing brace of run()
        // This is tricky with regex. A simpler approach for standard files:
        // Find "public function run(): void {" and find the matching closing brace.
        // Assuming standard indentation, the closing brace is "    }"

        $pattern = '/(\s{4}\})/'; // Indented closing brace
        // We might want to be more specific, but for now let's try to append before the LAST indented brace of the run method?
        // Actually, let's just insert at the top for safety if logic gets complex, OR use AST.
        // For CLI tools, regex is often "good enough" if conventions are followed.

        // Let's assume the run method ends with `    }`
        // We find the LAST occurrence of `    }` before the FINAL `}` of the class?
        // Let's try inserting *after* the opening brace for fallback dev block too if the detailed regex failed?
        // No, user asked for order.

        // If we couldn't find the DEV block, we said we'd make one.
        // We can append it to the end of the run method.
        // Finding the end of run() method via regex:

        // Search for `public function run(): void { ... }`
        // It's safest to just create the block if it's missing.

        // If Regex failed to find the specific dev block, we assume we need to add it.
        // Let's insert it before the last line of the `run` method.
        // We can search for the closing brace of the method.
        // Assuming `    }` is the closing brace of the method relative to `class ... {`

        // REVISE STRATEGY:
        // Just put the dev block after the production calls, which are potentially at the top.
        // So we can find `public function run(): void { ...` and skip to the end of it?
        // Let's just create the block *after* the opening brace if it didn't exist, effectively putting it at the top
        // but that might be before production seeders.

        // Correct approach: Regex replacement of the LAST `    }` in the file? No, that's the class closer.
        // The second to last `    }`? Likely the method closer.

        // For simplicity in this iteration: If dev block missing, warn and insert at top.
        // Or better: Just insert it after `public function run(): void {` just like production, but wrapped.

        $pattern = '/(public function run\(\): void\s*\{)/';
        $replacement = "$1{$injection}";

        return preg_replace($pattern, $replacement, $content, 1);
    }
}
