<?php

use App\Http\Controllers\Preferences\PreferencesController;
use Illuminate\Support\Facades\Route;

/**
 * Public Preferences Routes
 *
 * These routes are available to all users, including guests.
 * Used for theme and locale preferences that don't require authentication.
 */
Route::post('/preferences/theme', [PreferencesController::class, 'updateTheme'])->name('preferences.theme');
Route::post('/preferences/locale', [PreferencesController::class, 'updateLocale'])->name('preferences.locale');
