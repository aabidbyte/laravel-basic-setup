<?php

use App\Http\Controllers\Auth\EmailChangeVerificationController;
use Illuminate\Support\Facades\Route;

/**
 * Email Verification Routes
 *
 * Public routes for verifying email changes.
 */
Route::get('/email/verify-change/{token}', [EmailChangeVerificationController::class, 'verify'])
    ->name('email.change.verify');
