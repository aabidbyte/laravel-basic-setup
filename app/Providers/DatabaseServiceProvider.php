<?php

namespace App\Providers;

use App\Enums\Database\ConnectionType;
use App\Services\Database\DatabaseService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DatabaseService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->initializeDatabaseConnections();
        $this->initializeMacros();
    }

    public function initializeDatabaseConnections(): void
    {
        $connectionNames = Arr::map(ConnectionType::cases(), fn ($connection) => $connection->connectionName());

        $databaseConnections = Arr::only(Config::get('database.connections'), $connectionNames);
        Config::set('database.connections', $databaseConnections);
    }

    /**
     * Initialize macros.
     */
    protected function initializeMacros(): void
    {
        Schema::macro('createTable', function (string $table, callable $callback) {
            Schema::create($table, function (Blueprint $blueprint) use ($callback) {
                $blueprint->id();
                $blueprint->uuid('uuid')
                    ->unique();

                $callback($blueprint);

                $blueprint->timestampsTz();
                $blueprint->softDeletesTz();
            });
        });

        Schema::macro('createPivotTable', function (string $table, callable $callback) {
            Schema::create($table, function (Blueprint $blueprint) use ($callback) {
                $blueprint->id();
                $blueprint->uuid('uuid')
                    ->unique();

                $callback($blueprint);

                $blueprint->timestampsTz();
            });
        });
    }
}
