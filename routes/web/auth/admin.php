<?php

use App\Http\Controllers\Auth\ImpersonationController;

/**
 * Admin Routes
 *
 * Routes requiring admin-level permissions.
 * Includes error logs and other administrative features.
 */

// Error Logs management
require __DIR__ . '/admin/error-logs.php';

// Plans management
require __DIR__ . '/admin/plans.php';

// Features management
require __DIR__ . '/admin/features.php';

// Subscriptions management
require __DIR__ . '/admin/subscriptions.php';

// User Impersonation
Route::post('/stop-impersonating', [ImpersonationController::class, 'stop'])
    ->name('administration.instance.stop-impersonating');
