<?php

declare(strict_types=1);

namespace App\Support\Database;

/**
 * Data Object for database credentials.
 */
readonly class DatabaseCredentials
{
    /**
     * Create a new DatabaseCredentials instance.
     *
     * @param  string  $connection  The database connection type
     * @param  string  $host  The database host
     * @param  string  $port  The database port
     * @param  string  $database  The database name
     * @param  string  $username  The database username
     * @param  string  $password  The database password
     * @param  bool  $isMySQL8Plus  Whether using MySQL 8.0+ or MariaDB 10.4+
     */
    public function __construct(
        public string $connection,
        public string $host,
        public string $port,
        public string $database,
        public string $username,
        public string $password = '',
        public bool $isMySQL8Plus = false,
    ) {}

    /**
     * Create a new DatabaseCredentials instance from an array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            connection: $data['connection'] ?? 'mysql',
            host: $data['host'] ?? '127.0.0.1',
            port: $data['port'] ?? '3306',
            database: $data['database'] ?? '',
            username: $data['username'] ?? 'root',
            password: $data['password'] ?? '',
            isMySQL8Plus: $data['is_mysql8_plus'] ?? false,
        );
    }
}
