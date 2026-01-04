<?php

use App\Http\Controllers\Preferences\PreferencesController;
use Illuminate\Support\Facades\Route;

// Preferences routes (available to all users, including guests)
Route::post('/preferences/theme', [PreferencesController::class, 'updateTheme'])->name('preferences.theme');
Route::post('/preferences/locale', [PreferencesController::class, 'updateLocale'])->name('preferences.locale');

// Account activation route (public, for users activating their account)
Route::livewire('/activate/{token}', 'pages::auth.activate')
    ->name('auth.activate');

Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/web/auth/dashboard.php';
    require __DIR__ . '/web/auth/settings.php';
});
