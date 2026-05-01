<?php

declare(strict_types=1);

namespace App\Services\Database;

use App\Enums\Database\ConnectionType;
use App\Models\Master;
use App\Models\Tenant;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use Throwable;

class DatabaseService
{
    public const string PREFIX_CONNECTIONS = 'connection__';

    /**
     * Generate a hierarchical database name per tier.
     * Truncates to 64 chars.
     */
    public static ?string $landlordDatabaseNameOverride = null;

    public static function setLandlordDatabaseNameOverride(?string $name): void
    {
        self::$landlordDatabaseNameOverride = $name;
    }

    public function generateLandlordDatabaseName(): string
    {
        if (self::$landlordDatabaseNameOverride) {
            return self::$landlordDatabaseNameOverride;
        }

        if ($override = env('DB_LANDLORD_OVERRIDE')) {
            return $override;
        }

        if ($this->isRunningTests()) {
            return $this->generateTestingLandlordDatabaseName();
        }
        $appSlug = $this->generateDatabasesAppSlug();

        $landlordTag = ConnectionType::LANDLORD->tag();

        $dbName = "{$appSlug}_{$landlordTag}";

        $this->validateDatabaseNamePart($dbName);

        return $this->ensureDatabaseNameLength($dbName);
    }

    public function generateMasterDatabaseName(string $masterName): string
    {
        if ($this->isRunningTests()) {
            return $this->generateTestingMasterDatabaseName($masterName);
        }

        $masterSlug = Str::limit(ucfirst(Str::camel($masterName)), 15, '');
        $this->validateDatabaseNamePart($masterSlug);

        $dbName = "{$this->generateDatabasesAppSlug()}_" . ConnectionType::MASTER->tag() . "_{$masterSlug}";

        return $this->ensureUniqueDbName($dbName, ConnectionType::MASTER);
    }

    public function generateTenantDatabaseName(string $masterName, string $tenantName): string
    {
        if ($this->isRunningTests()) {
            return $this->generateTestingTenantDatabaseName($masterName, $tenantName);
        }

        $masterSlug = Str::limit(ucfirst(Str::camel($masterName)), 15, '');
        $tenantSlug = Str::limit(ucfirst(Str::camel($tenantName)), 15, '');

        $this->validateDatabaseNamePart($masterSlug);
        $this->validateDatabaseNamePart($tenantSlug);

        $dbName = "{$this->generateDatabasesAppSlug()}_" . ConnectionType::MASTER->tag() . "_{$masterSlug}_" . ConnectionType::TENANT->tag() . "_{$tenantSlug}";

        return $this->ensureUniqueDbName($dbName, ConnectionType::TENANT);
    }

    public function generateTestingLandlordDatabaseName(?string $token = null): string
    {
        $dbName = $this->generateDatabasesAppSlug($token) . '_' . ConnectionType::LANDLORD->tag();

        $this->validateDatabaseNamePart($dbName);

        return $this->ensureDatabaseNameLength($dbName);
    }

    public function generateTestingMasterDatabaseName(string $masterName, ?string $token = null): string
    {
        $masterSlug = Str::limit(ucfirst(Str::camel($masterName)), 15, '');
        $this->validateDatabaseNamePart($masterSlug);

        return $this->ensureDatabaseNameLength(
            $this->generateDatabasesAppSlug($token) . '_' . ConnectionType::MASTER->tag() . "_{$masterSlug}",
        );
    }

    public function generateTestingTenantDatabaseName(string $masterName, string $tenantName, ?string $token = null): string
    {
        $masterSlug = Str::limit(ucfirst(Str::camel($masterName)), 15, '');
        $tenantSlug = Str::limit(ucfirst(Str::camel($tenantName)), 15, '');

        $this->validateDatabaseNamePart($masterSlug);
        $this->validateDatabaseNamePart($tenantSlug);

        return $this->ensureDatabaseNameLength(
            $this->generateDatabasesAppSlug($token) . '_' . ConnectionType::MASTER->tag() . "_{$masterSlug}_" . ConnectionType::TENANT->tag() . "_{$tenantSlug}",
        );
    }

    public function getParallelTestingToken(): ?string
    {
        $token = getenv('TEST_TOKEN');

        if ($token === false || $token === '') {
            $token = $_SERVER['TEST_TOKEN'] ?? $_ENV['TEST_TOKEN'] ?? null;
        }

        if ($token === null || $token === '') {
            return null;
        }

        return (string) $token;
    }

    protected function ensureUniqueDbName(string $dbName, ConnectionType $type, int $suffixCounter = 0): string
    {
        $candidate = $suffixCounter > 0 ? "{$dbName}_{$suffixCounter}" : $dbName;
        $model = match ($type) {
            ConnectionType::MASTER => Master::class,
            ConnectionType::TENANT => Tenant::class,
            default => null,
        };

        try {
            if ($model && $model::query()->where('db_name', $candidate)->exists()) {
                return $this->ensureUniqueDbName($dbName, $type, $suffixCounter + 1);
            }
        } catch (Throwable) {
        }

        return $this->ensureDatabaseNameLength($candidate);
    }

    protected function generateDatabasesAppSlug(?string $testToken = null): string
    {
        $slug = Str::limit(ucfirst(Str::camel(config('app.name'))), 15, '');

        if ($this->isRunningTests()) {
            return "{$slug}_test";
        }

        return $slug;
    }

    protected function validateDatabaseNamePart(string $name): void
    {
        if (! preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) {
            throw new InvalidArgumentException("Database name part contains invalid characters: {$name}");
        }
    }

    protected function ensureDatabaseNameLength(string $name): string
    {
        if (\strlen($name) > 64) {
            throw new LogicException(
                "Database name exceeds 64 characters: {$name}",
            );
        }

        return $name;
    }

    /**
     * Create a dynamic, temporary connection for a specific database.
     * Returns the connection name.
     */
    public function createDynamicConnection(string $dbName, ConnectionType $type): string
    {
        // Generate a unique connection name based on type and database
        $connectionName = getDbConnectionName($dbName);

        if (Config::has("database.connections.{$connectionName}")) {
            return $connectionName;
        }

        $templateConnection = $type->connectionName();

        $template = Config::get("database.connections.{$templateConnection}");

        if (! $template) {
            throw new Exception("Template database connection ['{$templateConnection}'] not found in config/database.php");
        }

        // Clone template and override database
        $config = $template;
        $config['database'] = $dbName;

        // Register new connection config
        Config::set("database.connections.{$connectionName}", $config);

        return $connectionName;
    }

    /**
     * Execute a callback using a temporary root connection (no database selected).
     * Useful for CREATE/DROP DATABASE operations.
     */
    protected function executeAsRoot(ConnectionType $type, callable $callback): void
    {
        $templateConnection = $type->connectionName();

        $rootConfig = Config::get("database.connections.{$templateConnection}");

        if (! $rootConfig) {
            throw new Exception("Source connection ['{$templateConnection}'] not found in config/database.php");
        }

        // Create temporary root config with no database selected
        $tempConnectionName = "temp_root_{$templateConnection}";
        $rootConfig['database'] = null;

        Config::set("database.connections.{$tempConnectionName}", $rootConfig);

        $this->reconnect($tempConnectionName);

        $callback($tempConnectionName);

        $this->disconnect($tempConnectionName);
    }

    public function runAsRoot(ConnectionType $type, callable $callback): void
    {
        $this->executeAsRoot($type, $callback);
    }

    /**
     * Configure a dynamic connection and return its name.
     * Wrapper for createDynamicConnection for backward compatibility/helper usage.
     */
    public function configureConnection(string $dbName, ConnectionType $type = ConnectionType::TENANT): string
    {
        $connectionName = $this->createDynamicConnection($dbName, $type);

        $this->reconnect($connectionName);

        return $connectionName;
    }

    /**
     * Purge and disconnect a connection.
     */
    public function disconnect(string $connectionName): void
    {
        DB::purge($connectionName);
        DB::disconnect($connectionName);
    }

    /**
     * Reconnect is less relevant now with dynamic connections,
     * but kept for API compatibility if needed.
     */
    public function reconnect(string $connectionName): void
    {
        DB::purge($connectionName);
        DB::reconnect($connectionName);
    }

    public function purgeConnections(array $connectionNames): void
    {
        foreach ($connectionNames as $connectionName) {
            DB::purge($connectionName);
            DB::disconnect($connectionName);
        }
    }

    /**
     * Set a database as the default connection.
     */
    public function setDefaultConnection(string $dbName, ConnectionType $type = ConnectionType::TENANT): void
    {
        $connectionName = $this->configureConnection($dbName, $type);
        DB::setDefaultConnection($connectionName);
    }

    /**
     * Check if a database exists.
     */
    public function databaseExists(string $dbName, ConnectionType $type = ConnectionType::TENANT): bool
    {
        if (empty($dbName) || \strlen($dbName) > 64) {
            return false;
        }

        try {
            $connectionName = $this->createDynamicConnection($dbName, $type);
            DB::connection($connectionName)->getPdo();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Create a database if it doesn't exist.
     */
    public function createDatabase(string $dbName, ConnectionType $type = ConnectionType::TENANT): void
    {
        $this->validateDatabaseNamePart($dbName);

        $this->executeAsRoot($type, function ($connectionName) use ($dbName) {
            // Escaping for raw statement - dbName is validated via validateDatabaseNamePart
            DB::connection($connectionName)->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        });
    }

    /**
     * Drop a database if it exists.
     */
    public function wipeDatabase(string $dbName, ConnectionType $type = ConnectionType::TENANT): void
    {
        $this->validateDatabaseNamePart($dbName);

        $this->executeAsRoot($type, function ($connectionName) use ($dbName) {
            DB::connection($connectionName)->statement("DROP DATABASE IF EXISTS `{$dbName}`;");
        });
    }

    protected function isRunningTests(): bool
    {
        if (! app()->bound(Application::class)) {
            return false;
        }

        return app()->environment('testing');
    }
}
