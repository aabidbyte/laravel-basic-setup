<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class LogViewerServiceProvider extends ServiceProvider
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
        $this->registerGate();
    }

    /**
     * Register the Log Viewer gate.
     *
     * This gate determines who can access Log Viewer in non-local environments.
     */
    protected function registerGate(): void
    {
        Gate::define('viewLogViewer', function ($user = null) {
            if (isDevelopment()) {
                return true;
            }

            return in_array(optional($user)->email, [
                //
            ]);
        });
    }
}
