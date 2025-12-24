<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\DataTable\Configs\UsersDataTableConfig;
use App\Services\DataTable\Contracts\DataTableBuilderInterface;
use App\Services\DataTable\DataTableBuilder;
use App\Services\DataTable\DataTablePreferencesService;
use App\Services\DataTable\Services\FilterService;
use App\Services\DataTable\Services\SearchService;
use App\Services\DataTable\Services\SessionService;
use App\Services\DataTable\Services\SortService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for DataTable system
 */
class DataTableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind core services
        $this->app->bind(DataTableBuilderInterface::class, DataTableBuilder::class);

        // Bind supporting services as singletons
        $this->app->singleton(DataTablePreferencesService::class);
        $this->app->singleton(SearchService::class);
        $this->app->singleton(FilterService::class);
        $this->app->singleton(SortService::class);
        $this->app->singleton(SessionService::class);

        // Bind entity configurations
        $this->app->singleton(UsersDataTableConfig::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
