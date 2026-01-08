<?php

use Illuminate\Support\Facades\Route;

/**
 * Dashboard Routes
 *
 * Main application dashboard.
 * Requires authentication.
 */
Route::livewire('/', 'pages::dashboard')
    ->name('dashboard');
