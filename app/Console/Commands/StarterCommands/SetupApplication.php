<?php

namespace App\Console\Commands\StarterCommands;

use App\Console\Commands\StarterCommands\Support\DatabaseSetup;
use App\Console\Commands\StarterCommands\Support\EnvFileManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:application {--no-fresh : Keep existing data and run migrations without dropping tables}';

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

        // Ask if user wants to set up database
        $setupDatabase = confirm(
            label: 'Would you like to set up the database connection?',
            default: true,
            hint: 'This will collect database credentials and test the connection.',
        );

        if (! $setupDatabase) {
            info('Skipping database setup.');
            info('Don\'t forget to configure your database in the .env file.');
            $migrationCommand = $this->getMigrationCommand(true);
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
                hint: 'This will create all database tables.',
            );
        } else {
            // Database setup was skipped, skip migrations too
            $runMigrations = false;
            $migrationCommand = $this->getMigrationCommand(true);
            info("Skipping migrations. You can run them later with: {$migrationCommand} (after configuring the database)");
        }

        if ($runMigrations) {
            // Fresh is the default behavior, unless --no-fresh is specified
            $fresh = ! $this->option('no-fresh');

            if ($fresh) {
                warning('⚠️  WARNING: Migrations will run fresh, dropping all existing tables and deleting all data!');
                warning('⚠️  All existing data in the database will be permanently deleted.');
                warning('⚠️  This action cannot be undone.');

                if (! confirm(
                    label: 'Are you sure you want to continue?',
                    default: true,
                    hint: 'This will delete all existing data in the database. Use --no-fresh to keep existing data.',
                )) {
                    info('Cancelled. Migrations will not be run.');
                    $runMigrations = false;
                }
            }

            if ($runMigrations) {
                if ($fresh) {
                    info('Running fresh migrations (dropping all tables)...');
                } else {
                    info('Running migrations (keeping existing data)...');
                }

                try {
                    if ($fresh) {
                        $this->call('migrate:fresh', ['--force' => true]);
                    } else {
                        $this->call('migrate', ['--force' => true]);
                    }
                    info('✅ Migrations completed successfully!');
                } catch (Exception $e) {
                    error('❌ Migration failed: ' . $e->getMessage());

                    info('Please check the error above and try again.');

                    return self::FAILURE;
                }
            }
        } elseif (! $databaseSkipped) {
            $fresh = ! $this->option('no-fresh');
            $migrationCommand = $this->getMigrationCommand($fresh);
            info("Skipping migrations. You can run them later with: {$migrationCommand}");
        }

        // Generate application keys
        info('Generating application keys...');
        $this->call('app:generate-keys');

        info('✅ Setup completed!');

        info('Next steps:');
        info('1. Run: npm install');
        info('2. Run: npm run build');
        info('3. Start development: composer run dev');

        $this->call('optimize:clear');

        return self::SUCCESS;
    }

    /**
     * Get the appropriate migration command.
     */
    protected function getMigrationCommand(bool $fresh = true): string
    {
        $migrateCommand = $fresh ? 'migrate:fresh' : 'migrate';

        return "php artisan {$migrateCommand}";
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
        } catch (Exception $e) {
            return false;
        }
    }
}
