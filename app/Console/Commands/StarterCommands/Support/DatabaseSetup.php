<?php

namespace App\Console\Commands\StarterCommands\Support;

use App\Support\Database\DatabaseCredentials;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

use PDO;
use PDOException;

class DatabaseSetup
{
    public function __construct(
        protected EnvFileManager $envManager,
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
            hint: 'Select your database type.',
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
                default: config('database.connections.' . $connection . '.host', '127.0.0.1'),
                required: true,
                hint: 'The database server hostname or IP address.',
            );

            $dbPort = text(
                label: 'Database port',
                default: config('database.connections.' . $connection . '.port', $connection === 'pgsql' ? '5432' : '3306'),
                required: true,
                hint: 'The database server port.',
            );

            $dbDatabase = text(
                label: 'Database name',
                default: config('database.connections.' . $connection . '.database', 'laravel'),
                required: true,
                hint: 'The name of the database.',
            );

            $dbUsername = text(
                label: 'Database username',
                default: config('database.connections.' . $connection . '.username', 'root'),
                required: true,
                hint: 'The database username.',
            );

            $dbPassword = password(
                label: 'Database password',
                hint: 'The database password (leave empty if no password).',
            );

            // Ask about MySQL version if using MySQL/MariaDB
            if ($connection === 'mysql' || $connection === 'mariadb') {
                $isMySQL8Plus = confirm(
                    label: 'Are you using MySQL 8.0+ or MariaDB 10.4+?',
                    default: true,
                    hint: 'These versions use "caching_sha2_password" authentication by default.',
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
                hint: 'Path to the SQLite database file (will be created if it doesn\'t exist).',
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
        $creds = DatabaseCredentials::fromArray($credentials);

        // Check if database exists and create if needed (for MySQL/MariaDB/PostgreSQL)
        if ($creds->connection !== 'sqlite' && (\in_array($creds->connection, ['mysql', 'mariadb', 'pgsql'], true))) {
            $this->checkAndCreateDatabase($creds);
        }

        // Update .env file
        $envUpdates = [
            'DB_CONNECTION' => $creds->connection,
            'DB_DATABASE' => $creds->database,
        ];

        if ($creds->connection !== 'sqlite') {
            $envUpdates['DB_HOST'] = $creds->host;
            $envUpdates['DB_PORT'] = $creds->port;
            $envUpdates['DB_USERNAME'] = $creds->username;
            $envUpdates['DB_PASSWORD'] = $creds->password ?: '';
        }

        $this->envManager->update($envUpdates);

        info('✅ Database credentials saved to .env file');

        // Update Config facade and clear DB connection cache
        $this->updateConfigAndClearCache($creds);

        // Log the database credentials that Laravel will use
        $this->logDatabaseConfig($creds);
    }

    /**
     * Update Config facade with database credentials and clear DB connection cache.
     *
     * @param  array<string, mixed>  $credentials
     */
    protected function updateConfigAndClearCache(DatabaseCredentials|array $credentials): void
    {
        if (\is_array($credentials)) {
            $credentials = DatabaseCredentials::fromArray($credentials);
        }

        // Set database config values directly using Config facade
        Config::set('database.default', $credentials->connection);
        Config::set('database.connections.' . $credentials->connection . '.driver', $credentials->connection);
        Config::set('database.connections.' . $credentials->connection . '.host', $credentials->host);
        Config::set('database.connections.' . $credentials->connection . '.port', $credentials->port);
        Config::set('database.connections.' . $credentials->connection . '.database', $credentials->database);
        Config::set('database.connections.' . $credentials->connection . '.username', $credentials->username);
        Config::set('database.connections.' . $credentials->connection . '.password', $credentials->password ?: '');
        if ($credentials->connection === 'mysql' || $credentials->connection === 'mariadb') {
            Config::set('database.connections.' . $credentials->connection . '.charset', 'utf8mb4');
            Config::set('database.connections.' . $credentials->connection . '.collation', 'utf8mb4_unicode_ci');
        } elseif ($credentials->connection === 'pgsql') {
            Config::set('database.connections.' . $credentials->connection . '.charset', 'utf8');
        }

        // Clear DB connection cache to ensure new config is used
        DB::purge($credentials->connection);
    }

    /**
     * Test database connection.
     */
    public function testConnection(): bool
    {
        info('Testing database connection...');

        try {
            // Clear DB connection cache before testing
            $connection = Config::get('database.default');
            DB::purge($connection);

            // Test connection by trying to get database connection
            DB::connection()->getPdo();
            info('✅ Database connection successful!');

            return true;
        } catch (Exception $e) {
            error('❌ Database connection failed: ' . $e->getMessage());

            // Provide brief guidance for common MySQL errors
            $errorMessage = $e->getMessage();
            if (\str_contains($errorMessage, 'mysql_native_password')) {
                info('💡 MySQL authentication plugin issue detected.');
                info('   Update your MySQL user to use caching_sha2_password (default for MySQL 8.0+):');
                $connection = Config::get('database.default');
                $dbUsername = Config::get('database.connections.' . $connection . '.username');
                $dbHost = Config::get('database.connections.' . $connection . '.host');
                $dbPassword = Config::get('database.connections.' . $connection . '.password');
                info('   ALTER USER \'' . $dbUsername . '\'@\'' . $dbHost . '\' IDENTIFIED WITH caching_sha2_password BY \'' . $dbPassword . '\';');
                info('   FLUSH PRIVILEGES;');
            } elseif (\str_contains($errorMessage, 'Access denied')) {
                // Check if this is a Docker connection issue
                if (\preg_match('/@\'?(\d+\.\d+\.\d+\.\d+)\'?/', $errorMessage, $matches)) {
                    $connectingFrom = $matches[1];
                    // Check if it's a Docker network IP (common ranges: 172.16-31.x.x, 192.168.x.x, 10.x.x.x)
                    if (\preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $connectingFrom) ||
                        \preg_match('/^192\.168\./', $connectingFrom) ||
                        \preg_match('/^10\./', $connectingFrom)) {
                        info('💡 Docker network detected. The connection is coming from: ' . $connectingFrom);
                        $connection = Config::get('database.default');
                        $dbHost = Config::get('database.connections.' . $connection . '.host');
                        $dbUsername = Config::get('database.connections.' . $connection . '.username');

                        if ($dbHost === '127.0.0.1' || $dbHost === 'localhost') {
                            info('');
                            info('   If you\'re running Laravel in Docker, try one of these solutions:');
                            info('   1. Use "host.docker.internal" as the database host (Docker Desktop)');
                            info('   2. Use your Docker service name if MySQL is in the same Docker network');
                            info('   3. Grant MySQL access from any host (for Docker):');
                            info('      CREATE USER IF NOT EXISTS \'' . $dbUsername . '\'@\'%\' IDENTIFIED BY \'your_password\';');
                            info('      GRANT ALL PRIVILEGES ON *.* TO \'' . $dbUsername . '\'@\'%\';');
                            info('      FLUSH PRIVILEGES;');
                        } else {
                            info('');
                            info('   The MySQL user may not have permissions from this IP address.');
                            info('   Grant access from any host (for Docker):');
                            info('   CREATE USER IF NOT EXISTS \'' . $dbUsername . '\'@\'%\' IDENTIFIED BY \'your_password\';');
                            info('   GRANT ALL PRIVILEGES ON *.* TO \'' . $dbUsername . '\'@\'%\';');
                            info('   FLUSH PRIVILEGES;');
                        }
                    } else {
                        info('💡 Please verify your database username and password are correct.');
                        info('   Connection attempted from: ' . $connectingFrom);
                    }
                } else {
                    info('💡 Please verify your database username and password are correct.');
                }
            } elseif (\str_contains($errorMessage, 'Unknown database')) {
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
     * @param  array<string, mixed>|null  $credentials  Optional credentials to update Config before testing
     * @return string Returns 'success', 'retry_same', 'retry_new', or 'exit'
     */
    public function testConnectionWithRetry(?array $credentials = null): string
    {
        info('Testing database connection...');

        try {
            // Update Config facade if credentials are provided (for retry scenarios)
            if ($credentials !== null) {
                $this->updateConfigAndClearCache($credentials);
            } else {
                // Clear DB connection cache before testing
                $connection = Config::get('database.default');
                DB::purge($connection);
            }

            // Test connection by trying to get database connection
            DB::connection()->getPdo();
            info('✅ Database connection successful!');

            return 'success';
        } catch (Exception $e) {
            error('❌ Database connection failed: ' . $e->getMessage());

            // Provide brief guidance for common MySQL errors
            $errorMessage = $e->getMessage();
            if (\str_contains($errorMessage, 'mysql_native_password')) {
                info('💡 MySQL authentication plugin issue detected.');
                info('   Update your MySQL user to use caching_sha2_password (default for MySQL 8.0+):');
                $connection = Config::get('database.default');
                $dbUsername = Config::get('database.connections.' . $connection . '.username');
                $dbHost = Config::get('database.connections.' . $connection . '.host');
                $dbPassword = Config::get('database.connections.' . $connection . '.password');
                info('   ALTER USER \'' . $dbUsername . '\'@\'' . $dbHost . '\' IDENTIFIED WITH caching_sha2_password BY \'' . $dbPassword . '\';');
                info('   FLUSH PRIVILEGES;');
            } elseif (\str_contains($errorMessage, 'Access denied')) {
                // Check if this is a Docker connection issue
                if (\preg_match('/@\'?(\d+\.\d+\.\d+\.\d+)\'?/', $errorMessage, $matches)) {
                    $connectingFrom = $matches[1];
                    // Check if it's a Docker network IP (common ranges: 172.16-31.x.x, 192.168.x.x, 10.x.x.x)
                    if (\preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $connectingFrom) ||
                        \preg_match('/^192\.168\./', $connectingFrom) ||
                        \preg_match('/^10\./', $connectingFrom)) {
                        info('💡 Docker network detected. The connection is coming from: ' . $connectingFrom);
                        $connection = Config::get('database.default');
                        $dbHost = Config::get('database.connections.' . $connection . '.host');
                        $dbUsername = Config::get('database.connections.' . $connection . '.username');

                        if ($dbHost === '127.0.0.1' || $dbHost === 'localhost') {
                            info('');
                            info('   If you\'re running Laravel in Docker, try one of these solutions:');
                            info('   1. Use "host.docker.internal" as the database host (Docker Desktop)');
                            info('   2. Use your Docker service name if MySQL is in the same Docker network');
                            info('   3. Grant MySQL access from any host (for Docker):');
                            info('      CREATE USER IF NOT EXISTS \'' . $dbUsername . '\'@\'%\' IDENTIFIED BY \'your_password\';');
                            info('      GRANT ALL PRIVILEGES ON *.* TO \'' . $dbUsername . '\'@\'%\';');
                            info('      FLUSH PRIVILEGES;');
                        } else {
                            info('');
                            info('   The MySQL user may not have permissions from this IP address.');
                            info('   Grant access from any host (for Docker):');
                            info('   CREATE USER IF NOT EXISTS \'' . $dbUsername . '\'@\'%\' IDENTIFIED BY \'your_password\';');
                            info('   GRANT ALL PRIVILEGES ON *.* TO \'' . $dbUsername . '\'@\'%\';');
                            info('   FLUSH PRIVILEGES;');
                        }
                    } else {
                        info('💡 Please verify your database username and password are correct.');
                        info('   Connection attempted from: ' . $connectingFrom);
                    }
                } else {
                    info('💡 Please verify your database username and password are correct.');
                }
            } elseif (\str_contains($errorMessage, 'Unknown database')) {
                info('💡 The database does not exist. Please create it first.');
            }

            info('');

            // Offer retry options
            $retryOption = select(
                label: 'What would you like to do?',
                options: [
                    'retry_same' => 'Retry with the same credentials',
                    'retry_new' => 'Enter new database credentials',
                    'exit' => 'Skip and fix the connection manually later',
                ],
                default: 'retry_new',
                hint: 'You can also manually update the .env file and run the setup again.',
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
                info('Skipping database connection setup. You can manually update the .env file and try again.');
                info('Or run: php artisan migrate (after fixing the database connection)');

                return 'exit';
            }
        }
    }

    /**
     * Log database configuration.
     */
    protected function logDatabaseConfig(DatabaseCredentials $credentials): void
    {
        info('Database credentials that Laravel will use:');
        info('  Connection: ' . Config::get('database.connections.' . $credentials->connection . '.driver', $credentials->connection));
        info('  Host: ' . Config::get('database.connections.' . $credentials->connection . '.host', $credentials->host));
        info('  Port: ' . Config::get('database.connections.' . $credentials->connection . '.port', $credentials->port));
        info('  Database: ' . Config::get('database.connections.' . $credentials->connection . '.database', $credentials->database ?: '(empty)'));
        info('  Username: ' . Config::get('database.connections.' . $credentials->connection . '.username', $credentials->username ?: '(empty)'));
        $configPassword = Config::get('database.connections.' . $credentials->connection . '.password', '');
        info('  Password: ' . ($configPassword ?: '(empty)'));
        if ($credentials->connection === 'mysql' || $credentials->connection === 'mariadb') {
            info('  Charset: ' . Config::get('database.connections.' . $credentials->connection . '.charset', 'utf8mb4'));
            info('  Collation: ' . Config::get('database.connections.' . $credentials->connection . '.collation', 'utf8mb4_unicode_ci'));
        }
    }

    /**
     * Check if database exists and create it if needed.
     */
    protected function checkAndCreateDatabase(DatabaseCredentials $credentials): void
    {
        try {
            // Connect to the database server without specifying a database
            $dsn = match ($credentials->connection) {
                'mysql', 'mariadb' => "mysql:host={$credentials->host};port={$credentials->port}",
                'pgsql' => "pgsql:host={$credentials->host};port={$credentials->port}",
                default => throw new InvalidArgumentException("Unsupported connection type: {$credentials->connection}"),
            };

            $pdo = new PDO($dsn, $credentials->username, $credentials->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if database exists
            $databaseExists = false;
            if ($credentials->connection === 'mysql' || $credentials->connection === 'mariadb') {
                $stmt = $pdo->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ' . $pdo->quote($credentials->database));
                $databaseExists = $stmt->rowCount() > 0;
            } elseif ($credentials->connection === 'pgsql') {
                $stmt = $pdo->query('SELECT 1 FROM pg_database WHERE datname = ' . $pdo->quote($credentials->database));
                $databaseExists = $stmt->rowCount() > 0;
            }

            if (! $databaseExists) {
                info('');
                $createDatabase = confirm(
                    label: "Database '{$credentials->database}' does not exist. Would you like to create it?",
                    default: true,
                    hint: 'The database will be created with the specified charset and collation.',
                );

                if ($createDatabase) {
                    // Ask for charset and collation for MySQL/MariaDB
                    $charset = 'utf8mb4';
                    $collation = 'utf8mb4_unicode_ci';

                    if ($credentials->connection === 'mysql' || $credentials->connection === 'mariadb') {
                        $charset = text(
                            label: 'Database charset',
                            default: 'utf8mb4',
                            required: true,
                            hint: 'Character set for the database (e.g., utf8mb4, utf8).',
                        );

                        $collation = text(
                            label: 'Database collation',
                            default: 'utf8mb4_unicode_ci',
                            required: true,
                            hint: 'Collation for the database (e.g., utf8mb4_unicode_ci, utf8mb4_general_ci).',
                        );
                    }

                    // Create the database
                    try {
                        if ($credentials->connection === 'mysql' || $credentials->connection === 'mariadb') {
                            $pdo->exec("CREATE DATABASE `{$credentials->database}` CHARACTER SET {$charset} COLLATE {$collation}");
                        } elseif ($credentials->connection === 'pgsql') {
                            $pdo->exec("CREATE DATABASE \"{$credentials->database}\" ENCODING 'UTF8'");
                        }

                        info("✅ Database '{$credentials->database}' created successfully!");
                        if ($credentials->connection === 'mysql' || $credentials->connection === 'mariadb') {
                            info("   Charset: {$charset}");
                            info("   Collation: {$collation}");
                        }
                    } catch (PDOException $e) {
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
                info("✅ Database '{$credentials->database}' already exists.");
            }
        } catch (PDOException $e) {
            // If we can't connect to check, that's okay - we'll catch it in the connection test
            warning("Could not check if database exists: {$e->getMessage()}");
            info('Will attempt to connect to the database in the next step.');
        }
    }
}
