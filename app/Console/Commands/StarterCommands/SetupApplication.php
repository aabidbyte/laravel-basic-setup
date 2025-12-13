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

        // Ask if user wants to run migrations
        $runMigrations = confirm(
            label: 'Would you like to run database migrations?',
            default: true,
            hint: 'This will require database credentials.'
        );

        if (! $runMigrations) {
            info('Skipping database setup.');
            info('You can run migrations later with: php artisan migrate');
            info('Don\'t forget to configure your database in the .env file.');

            return self::SUCCESS;
        }

        $databaseSetup = new DatabaseSetup($envManager);

        // Collect database credentials
        $credentials = $databaseSetup->collectCredentials();

        // Configure database
        $databaseSetup->configure($credentials);

        // Test database connection
        if (! $databaseSetup->testConnection()) {
            return self::FAILURE;
        }

        // Run migrations
        $runMigrationsNow = confirm(
            label: 'Would you like to run migrations now?',
            default: true,
            hint: 'This will create all database tables.'
        );

        if ($runMigrationsNow) {
            info('Running migrations...');

            try {
                $this->call('migrate', ['--force' => true]);
                info('✅ Migrations completed successfully!');
            } catch (\Exception $e) {
                error('❌ Migration failed: '.$e->getMessage());

                info('Please check the error above and try again.');

                return self::FAILURE;
            }
        } else {
            info('Skipping migrations. You can run them later with: php artisan migrate');
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
}
