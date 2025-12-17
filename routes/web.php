<?php

use App\Http\Controllers\PreferencesController;
use Illuminate\Support\Facades\Route;

// Preferences routes (available to all users, including guests)
Route::post('/preferences/theme', [PreferencesController::class, 'updateTheme'])->name('preferences.theme');
Route::post('/preferences/locale', [PreferencesController::class, 'updateLocale'])->name('preferences.locale');

Route::middleware(['auth'])->group(function () {
    require __DIR__.'/web/auth/dashboard.php';
    require __DIR__.'/web/auth/settings.php';
});
