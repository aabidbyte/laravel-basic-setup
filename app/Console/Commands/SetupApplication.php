<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

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
        info('');

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

        // Ask if user wants to run migrations
        $runMigrations = confirm(
            label: 'Would you like to run database migrations?',
            default: true,
            hint: 'This will require database credentials.'
        );

        if (! $runMigrations) {
            info('');
            info('Skipping database setup.');
            info('You can run migrations later with: php artisan migrate');
            info('Don\'t forget to configure your database in the .env file.');

            return self::SUCCESS;
        }

        info('');
        info('Please provide your database credentials:');

        // Get database connection type
        $connection = select(
            label: 'Database connection type',
            options: [
                'mysql' => 'MySQL',
                'pgsql' => 'PostgreSQL',
                'sqlite' => 'SQLite',
                'mariadb' => 'MariaDB',
            ],
            default: 'mysql',
            hint: 'Select your database type.'
        );

        $dbHost = '127.0.0.1';
        $dbPort = '3306';
        $dbDatabase = '';
        $dbUsername = 'root';
        $dbPassword = '';

        // SQLite doesn't need host, port, username, password
        if ($connection !== 'sqlite') {
            // Get database credentials
            $dbHost = text(
                label: 'Database host',
                default: '127.0.0.1',
                required: true,
                hint: 'The database server hostname or IP address.'
            );

            $dbPort = text(
                label: 'Database port',
                default: $connection === 'pgsql' ? '5432' : '3306',
                required: true,
                hint: 'The database server port.'
            );

            $dbDatabase = text(
                label: 'Database name',
                default: '',
                required: true,
                hint: 'The name of the database.'
            );

            $dbUsername = text(
                label: 'Database username',
                default: 'root',
                required: true,
                hint: 'The database username.'
            );

            $dbPassword = password(
                label: 'Database password',
                hint: 'The database password (leave empty if no password).'
            );
        } else {
            // SQLite: ask for database file path
            $dbDatabase = text(
                label: 'Database file path',
                default: database_path('database.sqlite'),
                required: true,
                hint: 'Path to the SQLite database file (will be created if it doesn\'t exist).'
            );

            // Create SQLite database file if it doesn't exist
            if (! File::exists($dbDatabase)) {
                $directory = dirname($dbDatabase);
                if (! File::isDirectory($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
                File::put($dbDatabase, '');
                info("✅ Created SQLite database file: {$dbDatabase}");
            }
        }

        // Update .env file
        $envUpdates = [
            'DB_CONNECTION' => $connection,
            'DB_DATABASE' => $dbDatabase,
        ];

        if ($connection !== 'sqlite') {
            $envUpdates['DB_HOST'] = $dbHost;
            $envUpdates['DB_PORT'] = $dbPort;
            $envUpdates['DB_USERNAME'] = $dbUsername;
            $envUpdates['DB_PASSWORD'] = $dbPassword ?: '';
        }

        $this->updateEnvFile($envUpdates);

        info('');
        info('✅ Database credentials saved to .env file');

        // Test database connection
        info('');
        info('Testing database connection...');

        try {
            // Clear config cache to ensure new .env values are loaded
            $this->call('config:clear');

            // Test connection by trying to get database connection
            DB::connection()->getPdo();
            info('✅ Database connection successful!');
        } catch (\Exception $e) {
            error('❌ Database connection failed: '.$e->getMessage());
            info('');
            info('Please check your database credentials and try again.');
            info('You can manually update the .env file and run: php artisan migrate');

            return self::FAILURE;
        }

        // Run migrations
        info('');
        $runMigrationsNow = confirm(
            label: 'Would you like to run migrations now?',
            default: true,
            hint: 'This will create all database tables.'
        );

        if ($runMigrationsNow) {
            info('');
            info('Running migrations...');

            try {
                $this->call('migrate', ['--force' => true]);
                info('✅ Migrations completed successfully!');
            } catch (\Exception $e) {
                error('❌ Migration failed: '.$e->getMessage());
                info('');
                info('Please check the error above and try again.');

                return self::FAILURE;
            }
        } else {
            info('');
            info('Skipping migrations. You can run them later with: php artisan migrate');
        }

        info('');
        info('✅ Setup completed!');
        info('');
        info('Next steps:');
        info('1. Run: npm install');
        info('2. Run: php artisan install:stack (if not done already)');
        info('3. Run: npm run build');
        info('4. Start development: composer run dev');

        return self::SUCCESS;
    }

    /**
     * Update environment variables in .env file.
     *
     * @param  array<string, string>  $variables
     */
    protected function updateEnvFile(array $variables): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        foreach ($variables as $key => $value) {
            // Format the value for .env file
            $formattedValue = $this->formatEnvValue($value);

            // Replace existing value or add new one
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$formattedValue}",
                    $envContent
                );
            } else {
                // Add new variable at the end of the file
                $envContent .= "\n{$key}={$formattedValue}";
            }
        }

        File::put($envPath, $envContent);
    }

    /**
     * Format a value for .env file.
     */
    protected function formatEnvValue(string $value): string
    {
        // Empty values should remain empty (no quotes)
        if ($value === '') {
            return '';
        }

        // Escape backslashes and dollar signs
        $escaped = str_replace(['\\', '$'], ['\\\\', '\\$'], $value);

        // If value contains spaces, special characters, or starts with a number, wrap in quotes
        if (preg_match('/[\s#=]|^\d/', $escaped)) {
            return '"'.$escaped.'"';
        }

        return $escaped;
    }
}
