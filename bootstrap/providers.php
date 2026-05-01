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

return [
    AppServiceProvider::class,
    DatabaseServiceProvider::class,
    AccessServiceProvider::class,
    BladeServiceProvider::class,
    FortifyServiceProvider::class,
    FrontendPreferencesServiceProvider::class,
    I18nServiceProvider::class,
    LogViewerServiceProvider::class,
    MacroServiceProvider::class,
    ModelServiceProvider::class,
    SecurityServiceProvider::class,
    TelescopeServiceProvider::class,
    HorizonServiceProvider::class,
];
