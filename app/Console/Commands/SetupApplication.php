<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

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

        // Ask if user wants to use multi-tenancy
        $useMultiTenancy = confirm(
            label: 'Would you like to use multi-tenancy?',
            default: false,
            hint: 'This will install and configure Stancl/Tenancy package for multi-tenant support.'
        );

        if ($useMultiTenancy) {
            $result = $this->installMultiTenancy();
            if ($result !== self::SUCCESS) {
                return $result;
            }
        } else {
            // Add flag to .env indicating multi-tenancy is disabled
            $this->updateEnvFile(['MULTI_TENANCY_ENABLED' => 'false']);
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
        $isMySQL8Plus = false;

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

            // Ask about MySQL version if using MySQL/MariaDB
            if ($connection === 'mysql' || $connection === 'mariadb') {

                $isMySQL8Plus = confirm(
                    label: 'Are you using MySQL 8.0+ or MariaDB 10.4+?',
                    default: true,
                    hint: 'These versions use "caching_sha2_password" authentication by default.'
                );

                // Default to caching_sha2_password for MySQL 8.0+
                if ($isMySQL8Plus) {

                    info('✅ Will use "caching_sha2_password" authentication (default for MySQL 8.0+).');

                }
            }
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

        // Check if database exists and create if needed (for MySQL/MariaDB/PostgreSQL)
        if ($connection !== 'sqlite' && ($connection === 'mysql' || $connection === 'mariadb' || $connection === 'pgsql')) {
            $this->checkAndCreateDatabase($connection, $dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword);
        }

        $this->updateEnvFile($envUpdates);

        info('✅ Database credentials saved to .env file');

        // Clear config cache to ensure new .env values are loaded
        $this->call('config:clear');
        $this->call('config:cache');

        // Log the database credentials that Laravel will use (from reloaded config)
        info('Database credentials that Laravel will use:');
        info('  Connection: '.config('database.connections.'.$connection.'.driver', $connection));
        info('  Host: '.config('database.connections.'.$connection.'.host', $dbHost));
        info('  Port: '.config('database.connections.'.$connection.'.port', $dbPort));
        info('  Database: '.config('database.connections.'.$connection.'.database', $dbDatabase));
        info('  Username: '.config('database.connections.'.$connection.'.username', $dbUsername));
        info('  Password: '.(config('database.connections.'.$connection.'.password') ? '***'.str_repeat('*', min(8, strlen(config('database.connections.'.$connection.'.password')))) : '(empty)'));
        if ($connection === 'mysql' || $connection === 'mariadb') {
            info('  Charset: '.config('database.connections.'.$connection.'.charset', 'utf8mb4'));
            info('  Collation: '.config('database.connections.'.$connection.'.collation', 'utf8mb4_unicode_ci'));
        }

        // Test database connection

        info('Testing database connection...');

        try {
            // Test connection by trying to get database connection
            DB::connection()->getPdo();
            info('✅ Database connection successful!');
        } catch (\Exception $e) {
            error('❌ Database connection failed: '.$e->getMessage());

            // Provide brief guidance for common MySQL errors
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'mysql_native_password')) {
                info('💡 MySQL authentication plugin issue detected.');
                info('   Update your MySQL user to use caching_sha2_password (default for MySQL 8.0+):');
                info('   ALTER USER \''.$dbUsername.'\'@\''.$dbHost.'\' IDENTIFIED WITH caching_sha2_password BY \''.$dbPassword.'\';');
                info('   FLUSH PRIVILEGES;');

            } elseif (str_contains($errorMessage, 'Access denied')) {
                info('💡 Please verify your database username and password are correct.');

            } elseif (str_contains($errorMessage, 'Unknown database')) {
                info('💡 The database does not exist. Please create it first.');

            }

            info('You can manually update the .env file and try again.');
            info('Or run: php artisan migrate (after fixing the database connection)');

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

    /**
     * Install and configure multi-tenancy package.
     */
    protected function installMultiTenancy(): int
    {

        info('Installing multi-tenancy package...');

        // Install the package via composer
        info('Installing stancl/tenancy package...');
        $output = [];
        $returnCode = 0;
        $command = 'composer require stancl/tenancy --no-interaction 2>&1';
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            error('❌ Failed to install stancl/tenancy package.');
            error('Output: '.implode("\n", $output));

            info('Please run manually: composer require stancl/tenancy');
            info('You can continue with the setup and install multi-tenancy later.');

            return self::FAILURE;
        }

        info('✅ Package installed successfully');

        // Regenerate autoloader and clear caches

        info('Regenerating autoloader...');
        $projectRoot = base_path();
        exec("cd {$projectRoot} && composer dump-autoload --no-interaction 2>&1", $autoloadOutput, $autoloadReturnCode);

        if ($autoloadReturnCode !== 0) {
            warning('Warning: Autoloader regeneration had issues, but continuing...');
        }

        // Clear config cache to ensure new package is discovered
        $this->call('config:clear');
        $this->call('package:discover', ['--ansi' => true]);

        // Run tenancy:install command

        info('Setting up tenancy configuration...');

        // Use shell execution in a fresh process to ensure the command is available
        // This runs in a new PHP process where the newly installed package will be autoloaded
        $tenancyInstallOutput = [];
        $tenancyInstallReturnCode = 0;
        exec("cd {$projectRoot} && php artisan tenancy:install --no-interaction 2>&1", $tenancyInstallOutput, $tenancyInstallReturnCode);

        if ($tenancyInstallReturnCode !== 0) {
            error('❌ Failed to run tenancy:install');
            error('Output: '.implode("\n", $tenancyInstallOutput));

            info('Please run manually: php artisan tenancy:install');

            return self::FAILURE;
        }

        info('✅ Tenancy configuration files created');

        // Create Tenant model
        $this->createTenantModel();

        // Update tenancy config
        $this->updateTenancyConfig();

        // Register service provider
        $this->registerTenancyServiceProvider();

        // Organize migrations
        $this->organizeMigrations();

        // Update routes
        $this->updateRoutes();

        // Update .env file
        $this->updateEnvFile(['MULTI_TENANCY_ENABLED' => 'true']);

        info('✅ Multi-tenancy setup completed!');

        return self::SUCCESS;
    }

    /**
     * Create the Tenant model.
     */
    protected function createTenantModel(): void
    {
        $tenantModelPath = app_path('Models/Tenant.php');

        if (File::exists($tenantModelPath)) {
            info('Tenant model already exists, skipping creation.');

            return;
        }

        $tenantModelContent = <<<'PHP'
<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
}
PHP;

        File::put($tenantModelPath, $tenantModelContent);
        info('✅ Created Tenant model');
    }

    /**
     * Update tenancy configuration to use custom Tenant model.
     */
    protected function updateTenancyConfig(): void
    {
        $configPath = config_path('tenancy.php');

        if (! File::exists($configPath)) {
            warning('Tenancy config file not found. Please run: php artisan tenancy:install');

            return;
        }

        $configContent = File::get($configPath);

        // Update tenant_model - try different patterns for different package versions
        // Pattern 1: 'tenant_model' => ...
        if (preg_match("/'tenant_model'\s*=>\s*[^,)]+/", $configContent)) {
            $configContent = preg_replace(
                "/'tenant_model'\s*=>\s*[^,)]+/",
                "'tenant_model' => \\App\\Models\\Tenant::class",
                $configContent
            );
        }
        // Pattern 2: 'models' => ['tenant' => ...]
        elseif (preg_match("/'models'\s*=>\s*\[/", $configContent)) {
            if (preg_match("/'tenant'\s*=>\s*[^,)]+/", $configContent)) {
                $configContent = preg_replace(
                    "/'tenant'\s*=>\s*[^,)]+/",
                    "'tenant' => \\App\\Models\\Tenant::class",
                    $configContent
                );
            } else {
                $configContent = preg_replace(
                    "/('models'\s*=>\s*\[)/",
                    "$1\n        'tenant' => \\App\\Models\\Tenant::class,",
                    $configContent
                );
            }
        }

        // Update central domains based on APP_URL from .env
        $envPath = base_path('.env');
        $centralDomains = ['localhost', '127.0.0.1'];

        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            if (preg_match('/^APP_URL=(.+)$/m', $envContent, $matches)) {
                $appUrl = trim($matches[1], '"\'');
                // Try to extract domain from APP_URL
                if (preg_match('/https?:\/\/([^\/]+)/', $appUrl, $urlMatches)) {
                    $domain = $urlMatches[1];
                    if (! in_array($domain, $centralDomains)) {
                        $centralDomains[] = $domain;
                    }
                }
            }
        }

        $domainsString = "['".implode("', '", $centralDomains)."']";

        // Update central_domains - try different patterns
        // Pattern 1: Direct 'central_domains' => [...]
        if (preg_match("/'central_domains'\s*=>\s*\[[^\]]+\]/", $configContent)) {
            $configContent = preg_replace(
                "/'central_domains'\s*=>\s*\[[^\]]+\]/",
                "'central_domains' => {$domainsString}",
                $configContent
            );
        }
        // Pattern 2: Inside 'identification' => ['central_domains' => ...]
        elseif (preg_match("/'identification'\s*=>\s*\[/", $configContent)) {
            if (preg_match("/'central_domains'\s*=>\s*\[[^\]]+\]/", $configContent)) {
                $configContent = preg_replace(
                    "/'central_domains'\s*=>\s*\[[^\]]+\]/",
                    "'central_domains' => {$domainsString}",
                    $configContent
                );
            } else {
                // Add central_domains inside identification array
                $configContent = preg_replace(
                    "/('identification'\s*=>\s*\[)/",
                    "$1\n        'central_domains' => {$domainsString},",
                    $configContent
                );
            }
        }

        File::put($configPath, $configContent);
        info('✅ Updated tenancy configuration');
    }

    /**
     * Register TenancyServiceProvider in bootstrap/providers.php.
     */
    protected function registerTenancyServiceProvider(): void
    {
        $providersPath = base_path('bootstrap/providers.php');

        if (! File::exists($providersPath)) {
            warning('bootstrap/providers.php not found. Please register TenancyServiceProvider manually.');

            return;
        }

        $providersContent = File::get($providersPath);

        // Check if TenancyServiceProvider is already registered
        if (str_contains($providersContent, 'TenancyServiceProvider')) {
            info('TenancyServiceProvider already registered.');

            return;
        }

        // Add conditional registration after TelescopeServiceProvider and before VoltServiceProvider check
        $insertion = "// Only register TenancyServiceProvider if multi-tenancy is enabled\n";
        $insertion .= "if (class_exists(\\App\\Providers\\TenancyServiceProvider::class)) {\n";
        $insertion .= "    \$providers[] = App\\Providers\\TenancyServiceProvider::class;\n";
        $insertion .= "}\n\n";

        // Insert before the VoltServiceProvider check
        if (str_contains($providersContent, 'VoltServiceProvider')) {
            $providersContent = str_replace(
                '// Only register VoltServiceProvider if Livewire/Volt is installed',
                $insertion.'// Only register VoltServiceProvider if Livewire/Volt is installed',
                $providersContent
            );
        } else {
            // If no VoltServiceProvider check, add before return statement
            $providersContent = str_replace(
                "\nreturn \$providers;",
                "\n\n{$insertion}return \$providers;",
                $providersContent
            );
        }

        File::put($providersPath, $providersContent);
        info('✅ Registered TenancyServiceProvider');
    }

    /**
     * Organize migrations by moving user migration to tenant folder.
     */
    protected function organizeMigrations(): void
    {
        $usersMigration = database_path('migrations/0001_01_01_000000_create_users_table.php');
        $tenantMigrationsDir = database_path('migrations/tenant');

        if (! File::exists($usersMigration)) {
            info('Users migration not found, skipping migration organization.');

            return;
        }

        // Create tenant migrations directory if it doesn't exist
        if (! File::isDirectory($tenantMigrationsDir)) {
            File::makeDirectory($tenantMigrationsDir, 0755, true);
        }

        $destination = $tenantMigrationsDir.'/0001_01_01_000000_create_users_table.php';

        if (File::exists($destination)) {
            info('Users migration already in tenant folder, skipping.');

            return;
        }

        File::move($usersMigration, $destination);
        info('✅ Moved users migration to tenant migrations folder');
    }

    /**
     * Update routes to add comment about multi-tenancy routing.
     */
    protected function updateRoutes(): void
    {
        $routesPath = base_path('routes/web.php');

        if (! File::exists($routesPath)) {
            return;
        }

        $routesContent = File::get($routesPath);

        // Check if comment already exists
        if (str_contains($routesContent, 'multi-tenancy') || str_contains($routesContent, 'central_domains')) {
            info('Routes already have multi-tenancy comments.');

            return;
        }

        // Add comment at the top after PHP tag
        $comment = "\n// Multi-tenancy: Routes in this file are accessible on central domains only.\n";
        $comment .= "// Tenant routes should be defined in routes/tenant.php\n";

        // Insert after the last use statement
        // Use preg_quote to safely escape the namespace for regex
        $fortifyPattern = '/'.preg_quote('use Laravel\Fortify\Features;', '/').'/';

        if (preg_match($fortifyPattern, $routesContent)) {
            $routesContent = preg_replace(
                $fortifyPattern,
                '$0'.$comment,
                $routesContent
            );
        } else {
            // Fallback: insert after the last use statement or after opening PHP tag
            if (preg_match('/(use [^;]+;)\s*\n/', $routesContent, $matches)) {
                $routesContent = preg_replace(
                    '/(use [^;]+;)\s*\n/',
                    '$1'.$comment,
                    $routesContent,
                    1
                );
            } else {
                // Insert after opening PHP tag
                $routesContent = preg_replace(
                    '/^<\?php\s*\n/',
                    '<?php'.$comment,
                    $routesContent
                );
            }
        }

        File::put($routesPath, $routesContent);
        info('✅ Added multi-tenancy routing comments');
    }

    /**
     * Check if database exists and create it if needed.
     */
    protected function checkAndCreateDatabase(string $connection, string $host, string $port, string $database, string $username, string $password): void
    {
        try {
            // Connect to the database server without specifying a database
            $dsn = match ($connection) {
                'mysql', 'mariadb' => "mysql:host={$host};port={$port}",
                'pgsql' => "pgsql:host={$host};port={$port}",
                default => throw new \InvalidArgumentException("Unsupported connection type: {$connection}"),
            };

            $pdo = new \PDO($dsn, $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Check if database exists
            $databaseExists = false;
            if ($connection === 'mysql' || $connection === 'mariadb') {
                $stmt = $pdo->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '.$pdo->quote($database));
                $databaseExists = $stmt->rowCount() > 0;
            } elseif ($connection === 'pgsql') {
                $stmt = $pdo->query('SELECT 1 FROM pg_database WHERE datname = '.$pdo->quote($database));
                $databaseExists = $stmt->rowCount() > 0;
            }

            if (! $databaseExists) {
                info('');
                $createDatabase = confirm(
                    label: "Database '{$database}' does not exist. Would you like to create it?",
                    default: true,
                    hint: 'The database will be created with the specified charset and collation.'
                );

                if ($createDatabase) {
                    // Ask for charset and collation for MySQL/MariaDB
                    $charset = 'utf8mb4';
                    $collation = 'utf8mb4_unicode_ci';

                    if ($connection === 'mysql' || $connection === 'mariadb') {
                        $charset = text(
                            label: 'Database charset',
                            default: 'utf8mb4',
                            required: true,
                            hint: 'Character set for the database (e.g., utf8mb4, utf8).'
                        );

                        $collation = text(
                            label: 'Database collation',
                            default: 'utf8mb4_unicode_ci',
                            required: true,
                            hint: 'Collation for the database (e.g., utf8mb4_unicode_ci, utf8mb4_general_ci).'
                        );
                    }

                    // Create the database
                    try {
                        if ($connection === 'mysql' || $connection === 'mariadb') {
                            $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET {$charset} COLLATE {$collation}");
                        } elseif ($connection === 'pgsql') {
                            $pdo->exec("CREATE DATABASE \"{$database}\" ENCODING 'UTF8'");
                        }

                        info("✅ Database '{$database}' created successfully!");
                        if ($connection === 'mysql' || $connection === 'mariadb') {
                            info("   Charset: {$charset}");
                            info("   Collation: {$collation}");
                        }
                    } catch (\PDOException $e) {
                        error("❌ Failed to create database: {$e->getMessage()}");
                        info('');
                        info('Please create the database manually and try again.');
                        throw $e;
                    }
                } else {
                    info('');
                    info('Skipping database creation. Please create the database manually and try again.');
                }
            } else {
                info("✅ Database '{$database}' already exists.");
            }
        } catch (\PDOException $e) {
            // If we can't connect to check, that's okay - we'll catch it in the connection test
            warning("Could not check if database exists: {$e->getMessage()}");
            info('Will attempt to connect to the database in the next step.');
        }
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
