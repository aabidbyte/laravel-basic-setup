<?php

use Illuminate\Support\Facades\Route;

/**
 * Error Log Management Routes
 *
 * Admin interface for viewing and managing application error logs.
 */
Route::prefix('admin/errors')->name('admin.errors.')->group(function () {
    Route::view('/', 'pages.error-logs.index')->name('index');
    Route::livewire('/{errorLog}', 'pages::error-logs.show')->name('show');
});
