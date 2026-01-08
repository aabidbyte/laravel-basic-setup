<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Preferences\PreferencesController;
use Illuminate\Support\Facades\Route;

// Preferences routes (available to all users, including guests)
Route::post('/preferences/theme', [PreferencesController::class, 'updateTheme'])->name('preferences.theme');
Route::post('/preferences/locale', [PreferencesController::class, 'updateLocale'])->name('preferences.locale');

// Account activation route (public, for users activating their account)
Route::get('/activate/{token}', [AuthController::class, 'showActivationForm'])->name('auth.activate');
Route::post('/activate/{token}', [AuthController::class, 'activate']);

// Test error routes (development only)
if (app()->environment('local', 'development')) {
    Route::get('/test-error', function () {
        throw new \Exception('This is a test error to verify the error handling system.');
    })->name('test.error');

    Route::get('/test-error-404', function () {
        throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Test model not found');
    })->name('test.error.404');

    Route::get('/test-error-403', function () {
        throw new \Illuminate\Auth\Access\AuthorizationException('Test authorization error');
    })->name('test.error.403');
}

Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/web/auth/dashboard.php';
    require __DIR__ . '/web/auth/settings.php';
});
