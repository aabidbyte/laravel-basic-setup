<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * Account Activation Routes
 *
 * Public routes for users activating their account via token.
 * These routes must be accessible without authentication.
 */
Route::get('/activate/{token}', [AuthController::class, 'showActivationForm'])->name('auth.activate');
Route::post('/activate/{token}', [AuthController::class, 'activate'])->name('auth.activate.store');
