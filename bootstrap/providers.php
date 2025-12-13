<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
];

// Only register TenancyServiceProvider if multi-tenancy is enabled
if (class_exists(\App\Providers\TenancyServiceProvider::class)) {
    $providers[] = App\Providers\TenancyServiceProvider::class;
}

// Only register VoltServiceProvider if Livewire/Volt is installed
if (class_exists(\Livewire\Volt\Volt::class)) {
    $providers[] = App\Providers\VoltServiceProvider::class;
}

return $providers;
