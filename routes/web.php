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

Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/web/auth/dashboard.php';
    require __DIR__ . '/web/auth/settings.php';
});
