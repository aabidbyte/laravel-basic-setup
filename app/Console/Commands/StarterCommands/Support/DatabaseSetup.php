<?php

namespace App\Console\Commands\StarterCommands\Support;

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

class DatabaseSetup
{
    public function __construct(
        protected EnvFileManager $envManager
    ) {}

    /**
     * Collect database credentials from user.
     *
     * @return array<string, mixed>
     */
    public function collectCredentials(): array
    {
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

        return [
            'connection' => $connection,
            'host' => $dbHost,
            'port' => $dbPort,
            'database' => $dbDatabase,
            'username' => $dbUsername,
            'password' => $dbPassword,
            'is_mysql8_plus' => $isMySQL8Plus,
        ];
    }

    /**
     * Configure and save database settings.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function configure(array $credentials): void
    {
        $connection = $credentials['connection'];
        $dbHost = $credentials['host'];
        $dbPort = $credentials['port'];
        $dbDatabase = $credentials['database'];
        $dbUsername = $credentials['username'];
        $dbPassword = $credentials['password'];

        // Check if database exists and create if needed (for MySQL/MariaDB/PostgreSQL)
        if ($connection !== 'sqlite' && ($connection === 'mysql' || $connection === 'mariadb' || $connection === 'pgsql')) {
            $this->checkAndCreateDatabase($connection, $dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword);
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

        $this->envManager->update($envUpdates);

        info('✅ Database credentials saved to .env file');

        // Set database config values directly using Config facade
        Config::set('database.default', $connection);
        Config::set('database.connections.'.$connection.'.driver', $connection);
        Config::set('database.connections.'.$connection.'.host', $dbHost);
        Config::set('database.connections.'.$connection.'.port', $dbPort);
        Config::set('database.connections.'.$connection.'.database', $dbDatabase);
        Config::set('database.connections.'.$connection.'.username', $dbUsername);
        Config::set('database.connections.'.$connection.'.password', $dbPassword ?: '');
        if ($connection === 'mysql' || $connection === 'mariadb') {
            Config::set('database.connections.'.$connection.'.charset', 'utf8mb4');
            Config::set('database.connections.'.$connection.'.collation', 'utf8mb4_unicode_ci');
        } elseif ($connection === 'pgsql') {
            Config::set('database.connections.'.$connection.'.charset', 'utf8');
        }

        // Log the database credentials that Laravel will use
        $this->logDatabaseConfig($connection, $dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword);
    }

    /**
     * Test database connection.
     */
    public function testConnection(): bool
    {
        info('Testing database connection...');

        try {
            // Test connection by trying to get database connection
            DB::connection()->getPdo();
            info('✅ Database connection successful!');

            return true;
        } catch (\Exception $e) {
            error('❌ Database connection failed: '.$e->getMessage());

            // Provide brief guidance for common MySQL errors
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'mysql_native_password')) {
                info('💡 MySQL authentication plugin issue detected.');
                info('   Update your MySQL user to use caching_sha2_password (default for MySQL 8.0+):');
                $connection = Config::get('database.default');
                $dbUsername = Config::get('database.connections.'.$connection.'.username');
                $dbHost = Config::get('database.connections.'.$connection.'.host');
                $dbPassword = Config::get('database.connections.'.$connection.'.password');
                info('   ALTER USER \''.$dbUsername.'\'@\''.$dbHost.'\' IDENTIFIED WITH caching_sha2_password BY \''.$dbPassword.'\';');
                info('   FLUSH PRIVILEGES;');

            } elseif (str_contains($errorMessage, 'Access denied')) {
                info('💡 Please verify your database username and password are correct.');

            } elseif (str_contains($errorMessage, 'Unknown database')) {
                info('💡 The database does not exist. Please create it first.');

            }

            info('You can manually update the .env file and try again.');
            info('Or run: php artisan migrate (after fixing the database connection)');

            return false;
        }
    }

    /**
     * Test database connection with retry options.
     *
     * @return string Returns 'success', 'retry_same', 'retry_new', or 'exit'
     */
    public function testConnectionWithRetry(): string
    {
        info('Testing database connection...');

        try {
            // Test connection by trying to get database connection
            DB::connection()->getPdo();
            info('✅ Database connection successful!');

            return 'success';
        } catch (\Exception $e) {
            error('❌ Database connection failed: '.$e->getMessage());

            // Provide brief guidance for common MySQL errors
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'mysql_native_password')) {
                info('💡 MySQL authentication plugin issue detected.');
                info('   Update your MySQL user to use caching_sha2_password (default for MySQL 8.0+):');
                $connection = Config::get('database.default');
                $dbUsername = Config::get('database.connections.'.$connection.'.username');
                $dbHost = Config::get('database.connections.'.$connection.'.host');
                $dbPassword = Config::get('database.connections.'.$connection.'.password');
                info('   ALTER USER \''.$dbUsername.'\'@\''.$dbHost.'\' IDENTIFIED WITH caching_sha2_password BY \''.$dbPassword.'\';');
                info('   FLUSH PRIVILEGES;');

            } elseif (str_contains($errorMessage, 'Access denied')) {
                info('💡 Please verify your database username and password are correct.');

            } elseif (str_contains($errorMessage, 'Unknown database')) {
                info('💡 The database does not exist. Please create it first.');

            }

            info('');

            // Offer retry options
            $retryOption = select(
                label: 'What would you like to do?',
                options: [
                    'retry_same' => 'Retry with the same credentials',
                    'retry_new' => 'Enter new database credentials',
                    'exit' => 'Exit and fix the connection manually',
                ],
                default: 'retry_new',
                hint: 'You can also manually update the .env file and run the setup again.'
            );

            if ($retryOption === 'retry_same') {
                warning('Retrying connection with the same credentials...');
                info('');

                return 'retry_same';
            } elseif ($retryOption === 'retry_new') {
                info('Please provide new database credentials.');
                info('');

                return 'retry_new';
            } else {
                info('Exiting setup. You can manually update the .env file and try again.');
                info('Or run: php artisan migrate (after fixing the database connection)');

                return 'exit';
            }
        }
    }

    /**
     * Log database configuration.
     */
    protected function logDatabaseConfig(string $connection, string $dbHost, string $dbPort, string $dbDatabase, string $dbUsername, string $dbPassword): void
    {
        info('Database credentials that Laravel will use:');
        info('  Connection: '.Config::get('database.connections.'.$connection.'.driver', $connection));
        info('  Host: '.Config::get('database.connections.'.$connection.'.host', $dbHost));
        info('  Port: '.Config::get('database.connections.'.$connection.'.port', $dbPort));
        info('  Database: '.Config::get('database.connections.'.$connection.'.database', $dbDatabase ?: '(empty)'));
        info('  Username: '.Config::get('database.connections.'.$connection.'.username', $dbUsername ?: '(empty)'));
        $configPassword = Config::get('database.connections.'.$connection.'.password', '');
        info('  Password: '.($configPassword ? '***'.str_repeat('*', min(8, strlen($configPassword))) : '(empty)'));
        if ($connection === 'mysql' || $connection === 'mariadb') {
            info('  Charset: '.Config::get('database.connections.'.$connection.'.charset', 'utf8mb4'));
            info('  Collation: '.Config::get('database.connections.'.$connection.'.collation', 'utf8mb4_unicode_ci'));
        }
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
            \Laravel\Prompts\warning("Could not check if database exists: {$e->getMessage()}");
            info('Will attempt to connect to the database in the next step.');
        }
    }
}
