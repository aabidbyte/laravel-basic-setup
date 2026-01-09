<?php

use Illuminate\Support\Facades\Route;

// Public routes (no auth required)
require __DIR__ . '/web/public/preferences.php';
require __DIR__ . '/web/public/activation.php';

// Development-only routes
if (app()->environment('local', 'development')) {
    require __DIR__ . '/web/dev/development.php';
}

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/web/auth/dashboard.php';
    require __DIR__ . '/web/auth/notifications.php';
    require __DIR__ . '/web/auth/users.php';
    require __DIR__ . '/web/auth/roles.php';
    require __DIR__ . '/web/auth/teams.php';
    require __DIR__ . '/web/auth/settings.php';
    require __DIR__ . '/web/auth/admin.php';
});
