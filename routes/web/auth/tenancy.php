<?php

declare(strict_types=1);

use App\Http\Controllers\Tenancy\TenantSwitchController;
use Illuminate\Support\Facades\Route;

Route::get('/tenants/switch/{tenant}', TenantSwitchController::class)
    ->name('tenants.switch')
    ->middleware(['universal', 'auth']);

Route::middleware(['auth'])->group(function () {
    // Platform Administration - Tenants CRUD
    Route::view('/tenants', 'pages.tenants.index')
        ->name('tenants.index');

    Route::livewire('/tenants/create', 'pages::tenants.edit')
        ->name('tenants.create');

    Route::livewire('/tenants/settings/{tenant?}', 'pages::tenants.edit')
        ->name('tenants.settings.edit');

    Route::livewire('/tenants/{tenant}/subscriptions', 'pages::tenants.subscriptions')
        ->name('tenants.subscriptions');
});
