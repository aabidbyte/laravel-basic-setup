<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! isProduction());

        // In testing, ensure central connection uses the same database as the default mysql connection
        if (app()->environment('testing')) {
            $defaultDatabase = config('database.connections.mysql.database');
            config(['database.connections.central.database' => $defaultDatabase]);
        }
    }
}
