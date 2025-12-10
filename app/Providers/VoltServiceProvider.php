<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class VoltServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only boot Volt if Livewire and Volt packages are installed
        if (!class_exists(\Livewire\Volt\Volt::class)) {
            return;
        }

        \Livewire\Volt\Volt::mount([
            config('livewire.view_path', resource_path('views/livewire')),
            resource_path('views/pages'),
        ]);
    }
}
