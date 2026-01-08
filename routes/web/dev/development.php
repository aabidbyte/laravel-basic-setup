<?php

use Illuminate\Support\Facades\Route;

/**
 * Development Routes
 *
 * Routes available only in local/development environments.
 * Used for testing error handling, debugging tools, etc.
 *
 * These routes are conditionally loaded only when:
 * - app()->environment('local', 'development') returns true
 */
Route::get('/test-error', function () {
    throw new \Exception('This is a test error to verify the error handling system.');
})->name('test.error');

Route::get('/test-error-404', function () {
    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Test model not found');
})->name('test.error.404');

Route::get('/test-error-403', function () {
    throw new \Illuminate\Auth\Access\AuthorizationException('Test authorization error');
})->name('test.error.403');
