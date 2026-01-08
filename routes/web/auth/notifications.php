<?php

use Illuminate\Support\Facades\Route;

/**
 * Notification Routes
 *
 * Routes for the user notification center.
 * Requires authentication.
 */
Route::livewire('/notifications', 'pages::notifications.index')
    ->name('notifications.index');
