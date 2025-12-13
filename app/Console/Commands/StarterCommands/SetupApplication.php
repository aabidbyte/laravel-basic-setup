<?php

namespace App\Console\Commands\StarterCommands;

use App\Console\Commands\StarterCommands\Support\DatabaseSetup;
use App\Console\Commands\StarterCommands\Support\EnvFileManager;
use App\Console\Commands\StarterCommands\Support\MultiTenancyCleanup;
use App\Console\Commands\StarterCommands\Support\MultiTenancySetup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive setup for the application including database configuration and migrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        info('Welcome to Laravel Basic Setup!');

        // Ensure .env file exists
        if (! File::exists(base_path('.env'))) {
            if (File::exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), base_path('.env'));
                info('✅ Created .env file from .env.example');
            } else {
                error('❌ .env.example file not found. Please create a .env file manually.');

                return self::FAILURE;
            }
        }

        $envManager = new EnvFileManager;

        // Ask if user wants to use multi-tenancy
        $useMultiTenancy = confirm(
            label: 'Would you like to use multi-tenancy?',
            default: false,
            hint: 'This will install and configure Stancl/Tenancy package for multi-tenant support.'
        );

        if ($useMultiTenancy) {
            $multiTenancySetup = new MultiTenancySetup($envManager, $this);
            $result = $multiTenancySetup->install();
            if ($result !== self::SUCCESS) {
                return $result;
            }
        } else {
            // Clean up multi-tenancy code if it was previously enabled
            $multiTenancyCleanup = new MultiTenancyCleanup($this);
            $multiTenancyCleanup->cleanup();
            // Add flag to .env indicating multi-tenancy is disabled
            $envManager->update(['MULTI_TENANCY_ENABLED' => 'false']);
        }

        // Ask if user wants to set up database
        $setupDatabase = confirm(
            label: 'Would you like to set up the database connection?',
            default: true,
            hint: 'This will collect database credentials and test the connection.'
        );

        if (! $setupDatabase) {
            info('Skipping database setup.');
            info('Don\'t forget to configure your database in the .env file.');
            $migrationCommand = $this->getMigrationCommand($useMultiTenancy);
            info("You can run migrations later with: {$migrationCommand}");

            return self::SUCCESS;
        }

        $databaseSetup = new DatabaseSetup($envManager);

        // Collect database credentials and test connection with retry options
        $connectionSuccessful = false;
        $databaseSkipped = false;
        $credentials = null;

        while (! $connectionSuccessful && ! $databaseSkipped) {
            // Only collect credentials if we don't have them or user wants new ones
            if ($credentials === null) {
                // Collect database credentials
                $credentials = $databaseSetup->collectCredentials();

                // Configure database
                $databaseSetup->configure($credentials);
            }

            // Test database connection with retry options
            // Always pass credentials to ensure Config is updated before testing
            $connectionResult = $databaseSetup->testConnectionWithRetry($credentials);
            if ($connectionResult === 'success') {
                $connectionSuccessful = true;
            } elseif ($connectionResult === 'retry_new') {
                // Reset credentials to collect new ones
                $credentials = null;
            } elseif ($connectionResult === 'retry_same') {
                // Retry with same credentials - Config will be re-set before testing
                // No need to reset credentials
            } else {
                // User chose to skip database connection setup
                info('Skipping database connection setup. You can configure it later in the .env file.');
                $databaseSkipped = true;
            }
        }

        // Only ask about migrations if database connection was successful
        if ($connectionSuccessful) {
            // Ask if user wants to run migrations
            $runMigrations = confirm(
                label: 'Would you like to run database migrations?',
                default: true,
                hint: 'This will create all database tables.'
            );
        } else {
            // Database setup was skipped, skip migrations too
            $runMigrations = false;
            $migrationCommand = $this->getMigrationCommand($useMultiTenancy);
            info("Skipping migrations. You can run them later with: {$migrationCommand} (after configuring the database)");
        }

        if ($runMigrations) {
            info('Running migrations...');

            try {
                // Check if tenancy command exists, multi-tenancy is enabled, and tenant migrations exist
                $hasTenantMigrations = $this->hasTenantMigrations();
                $useTenancyCommand = $useMultiTenancy && $this->commandExists('tenants:migrate') && $hasTenantMigrations;

                if ($useTenancyCommand) {
                    info('Running central database migrations...');
                    $this->call('migrate', ['--force' => true]);
                    info('Running tenant migrations...');
                    $this->call('tenants:migrate', ['--force' => true]);
                } else {
                    // Run central migrations only
                    $this->call('migrate', ['--force' => true]);
                }
                info('✅ Migrations completed successfully!');
            } catch (\Exception $e) {
                error('❌ Migration failed: '.$e->getMessage());

                info('Please check the error above and try again.');

                return self::FAILURE;
            }
        } elseif (! $databaseSkipped) {
            $migrationCommand = $this->getMigrationCommand($useMultiTenancy);
            info("Skipping migrations. You can run them later with: {$migrationCommand}");
        }

        info('✅ Setup completed!');

        info('Next steps:');
        info('1. Run: npm install');
        info('2. Run: php artisan install:stack (if not done already)');
        info('3. Run: npm run build');
        info('4. Start development: composer run dev');

        $this->call('optimize:clear');

        return self::SUCCESS;
    }

    /**
     * Get the appropriate migration command based on multi-tenancy status.
     */
    protected function getMigrationCommand(bool $useMultiTenancy): string
    {
        if ($useMultiTenancy && $this->commandExists('tenants:migrate') && $this->hasTenantMigrations()) {
            return 'php artisan tenants:migrate && php artisan migrate';
        }

        return 'php artisan migrate';
    }

    /**
     * Check if tenant migrations exist.
     */
    protected function hasTenantMigrations(): bool
    {
        $tenantMigrationsDir = database_path('migrations/tenant');

        if (! File::isDirectory($tenantMigrationsDir)) {
            return false;
        }

        $tenantMigrations = File::glob($tenantMigrationsDir.'/*.php');

        return ! empty($tenantMigrations);
    }

    /**
     * Check if a command exists in Artisan.
     */
    protected function commandExists(string $command): bool
    {
        try {
            $artisan = $this->getApplication();
            if ($artisan === null) {
                return false;
            }

            return $artisan->has($command);
        } catch (\Exception $e) {
            return false;
        }
    }
}
