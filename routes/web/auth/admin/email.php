<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;

/**
 * Email Admin Routes
 *
 * Routes for managing email templates and layouts.
 * Both use unified EMAIL_TEMPLATES permissions (layouts are templates with is_layout=true).
 */
Route::prefix('admin/email')->name('admin.email.')->group(function () {
    // Email Templates (Contents)
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::view('/', 'pages.admin.email.templates.index')
            ->middleware('can:' . Permissions::VIEW_EMAIL_TEMPLATES)
            ->name('index');

        Route::livewire('/create', 'pages::admin.email.templates.create')
            ->middleware('can:' . Permissions::CREATE_EMAIL_TEMPLATES)
            ->name('create');

        Route::livewire('/{template}', 'pages::admin.email.templates.show')
            ->middleware('can:' . Permissions::VIEW_EMAIL_TEMPLATES)
            ->name('show');

        Route::livewire('/{template}/edit', 'pages::admin.email.templates.edit')
            ->middleware('can:' . Permissions::EDIT_EMAIL_TEMPLATES)
            ->name('edit');

        Route::livewire('/{template}/settings', 'pages::admin.email.templates.settings')
            ->middleware('can:' . Permissions::EDIT_EMAIL_TEMPLATES)
            ->name('settings');
    });

    // Email Layouts (use unified EMAIL_TEMPLATES permissions)
    Route::prefix('layouts')->name('layouts.')->group(function () {
        Route::view('/', 'pages.admin.email.layouts.index')
            ->middleware('can:' . Permissions::VIEW_EMAIL_TEMPLATES)
            ->name('index');

        Route::livewire('/create', 'pages::admin.email.layouts.create')
            ->middleware('can:' . Permissions::CREATE_EMAIL_TEMPLATES)
            ->name('create');

        Route::livewire('/{layout}', 'pages::admin.email.layouts.show')
            ->middleware('can:' . Permissions::VIEW_EMAIL_TEMPLATES)
            ->name('show');

        Route::livewire('/{layout}/edit', 'pages::admin.email.layouts.edit')
            ->middleware('can:' . Permissions::EDIT_EMAIL_TEMPLATES)
            ->name('edit');
    });
});
