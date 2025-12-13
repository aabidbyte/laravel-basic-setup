<?php

namespace App\Console\Commands\StarterCommands\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;

class MultiTenancyCleanup
{
    public function __construct(
        protected Command $command
    ) {}

    /**
     * Clean up all multi-tenancy related code and files.
     */
    public function cleanup(): void
    {
        info('Cleaning up multi-tenancy code...');

        // Remove Tenant model
        $tenantModelPath = app_path('Models/Tenant.php');
        if (File::exists($tenantModelPath)) {
            File::delete($tenantModelPath);
            info('✅ Removed Tenant model');
        }

        // Remove tenancy config file
        $tenancyConfigPath = config_path('tenancy.php');
        if (File::exists($tenancyConfigPath)) {
            File::delete($tenancyConfigPath);
            info('✅ Removed tenancy config file');
        }

        // Remove tenant routes file
        $tenantRoutesPath = base_path('routes/tenant.php');
        if (File::exists($tenantRoutesPath)) {
            File::delete($tenantRoutesPath);
            info('✅ Removed tenant routes file');
        }

        // Remove TenancyServiceProvider registration from bootstrap/providers.php
        $this->unregisterTenancyServiceProvider();

        // Clean up routes/web.php - remove multi-tenancy comments
        $this->cleanupRoutesComments();

        // Clean up bootstrap/app.php - remove tenant route registration if exists
        $this->cleanupBootstrapAppTenantRoutes();

        // Move tenant migrations back to main migrations folder
        $this->restoreTenantMigrations();

        // Try to remove TenancyServiceProvider file if it exists
        $tenancyProviderPath = app_path('Providers/TenancyServiceProvider.php');
        if (File::exists($tenancyProviderPath)) {
            File::delete($tenancyProviderPath);
            info('✅ Removed TenancyServiceProvider');
        }

        // Clear config cache
        $this->command->call('config:clear');

        info('✅ Multi-tenancy cleanup completed');
    }

    /**
     * Unregister TenancyServiceProvider from bootstrap/providers.php.
     */
    protected function unregisterTenancyServiceProvider(): void
    {
        $providersPath = base_path('bootstrap/providers.php');

        if (! File::exists($providersPath)) {
            return;
        }

        $providersContent = File::get($providersPath);

        // Check if TenancyServiceProvider registration exists
        if (! str_contains($providersContent, 'TenancyServiceProvider')) {
            return;
        }

        // Remove the conditional registration block
        $pattern = '/\/\/ Only register TenancyServiceProvider if multi-tenancy is enabled\n';
        $pattern .= 'if \(class_exists\(\\\\App\\\\Providers\\\\TenancyServiceProvider::class\)\) \{\n';
        $pattern .= '    \$providers\[\] = App\\\\Providers\\\\TenancyServiceProvider::class;\n';
        $pattern .= '}\n\n?/';

        $providersContent = preg_replace($pattern, '', $providersContent);

        File::put($providersPath, $providersContent);
        info('✅ Removed TenancyServiceProvider registration');
    }

    /**
     * Clean up multi-tenancy comments from routes/web.php.
     */
    protected function cleanupRoutesComments(): void
    {
        $routesPath = base_path('routes/web.php');

        if (! File::exists($routesPath)) {
            return;
        }

        $routesContent = File::get($routesPath);

        // Remove multi-tenancy related comments
        $patterns = [
            '/\/\/ Multi-tenancy: Routes in this file are accessible on central domains only\.\n/',
            '/\/\/ Tenant routes should be defined in routes\/tenant\.php\n/',
        ];

        foreach ($patterns as $pattern) {
            $routesContent = preg_replace($pattern, '', $routesContent);
        }

        File::put($routesPath, $routesContent);
        info('✅ Cleaned up multi-tenancy comments from routes');
    }

    /**
     * Clean up tenant route registration from bootstrap/app.php.
     */
    protected function cleanupBootstrapAppTenantRoutes(): void
    {
        $appPath = base_path('bootstrap/app.php');

        if (! File::exists($appPath)) {
            return;
        }

        $appContent = File::get($appPath);

        // Check if tenant routes are registered
        if (! str_contains($appContent, 'tenant.php')) {
            return;
        }

        // Remove tenant route registration from withRouting
        // Pattern: tenant: __DIR__.'/../routes/tenant.php',
        $appContent = preg_replace(
            '/,\s*tenant:\s*__DIR__\.\'\/\.\.\/routes\/tenant\.php\'\s*,?\s*\n/',
            '',
            $appContent
        );

        // Also handle if it's the last item in withRouting
        $appContent = preg_replace(
            '/,\s*tenant:\s*__DIR__\.\'\/\.\.\/routes\/tenant\.php\'/',
            '',
            $appContent
        );

        File::put($appPath, $appContent);
        info('✅ Cleaned up tenant route registration from bootstrap/app.php');
    }

    /**
     * Move tenant migrations back to main migrations folder.
     */
    protected function restoreTenantMigrations(): void
    {
        $tenantMigrationsDir = database_path('migrations/tenant');
        $mainMigrationsDir = database_path('migrations');

        if (! File::isDirectory($tenantMigrationsDir)) {
            return;
        }

        $tenantMigrations = File::files($tenantMigrationsDir);
        $movedCount = 0;

        foreach ($tenantMigrations as $migration) {
            $filename = $migration->getFilename();
            $destination = $mainMigrationsDir.'/'.$filename;

            // Only move if it doesn't already exist in main migrations
            if (! File::exists($destination)) {
                File::move($migration->getPathname(), $destination);
                $movedCount++;
            } else {
                // If it exists, just remove from tenant folder
                File::delete($migration->getPathname());
            }
        }

        if ($movedCount > 0) {
            info("✅ Moved {$movedCount} migration(s) back to main migrations folder");
        }

        // Remove tenant migrations directory if empty
        if (empty(File::files($tenantMigrationsDir)) && File::isDirectory($tenantMigrationsDir)) {
            File::deleteDirectory($tenantMigrationsDir);
            info('✅ Removed empty tenant migrations directory');
        }
    }
}
