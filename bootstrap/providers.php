<?php

use App\Providers\AccessServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\BladeServiceProvider;
use App\Providers\DatabaseServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\FrontendPreferencesServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\I18nServiceProvider;
use App\Providers\LogViewerServiceProvider;
use App\Providers\MacroServiceProvider;
use App\Providers\ModelServiceProvider;
use App\Providers\SecurityServiceProvider;
use App\Providers\TelescopeServiceProvider;
use App\Providers\TenancyServiceProvider;
use App\Providers\VoltServiceProvider;

return [
    AccessServiceProvider::class,
    AppServiceProvider::class,
    BladeServiceProvider::class,
    DatabaseServiceProvider::class,
    FortifyServiceProvider::class,
    FrontendPreferencesServiceProvider::class,
    HorizonServiceProvider::class,
    I18nServiceProvider::class,
    LogViewerServiceProvider::class,
    MacroServiceProvider::class,
    ModelServiceProvider::class,
    SecurityServiceProvider::class,
    TelescopeServiceProvider::class,
    TenancyServiceProvider::class,
    VoltServiceProvider::class,
];
