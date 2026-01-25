<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Email Templates Routes
|--------------------------------------------------------------------------
|
| Unified management for Email Templates (both layouts and content).
| Layouts and content are distinguished by the `is_layout` boolean.
|
*/

Route::prefix('email-templates')->name('emailTemplates.')->group(function () {
    // Contents routes
    Route::prefix('contents')->name('contents.')->group(function () {
        Route::view('/', 'pages.emailTemplates.index')
            ->middleware('can:' . Permissions::VIEW_EMAIL_TEMPLATES())
            ->name('index');
    });

    // Layouts routes
    Route::prefix('layouts')->name('layouts.')->group(function () {
        Route::view('/', 'pages.emailTemplates.index')
            ->middleware('can:' . Permissions::VIEW_EMAIL_TEMPLATES())
            ->name('index');
    });

    // Unified Settings (Create + Edit)
    Route::livewire('/settings/{template?}', 'pages::emailTemplates.edit-settings')
        ->name('settings.edit');

    // Unified Builder (Create + Edit)
    Route::livewire('/builder/{template?}', 'pages::emailTemplates.edit-builder')
        ->name('builder.edit');

    // Show Template
    Route::livewire('/{template}', 'pages::emailTemplates.show')
        ->middleware('can:' . Permissions::VIEW_EMAIL_TEMPLATES())
        ->name('show');
});
