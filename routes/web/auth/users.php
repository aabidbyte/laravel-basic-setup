<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;

/**
 * User Management Routes
 *
 * CRUD routes for user management.
 * All routes require authentication and appropriate permissions.
 */

// Users list
Route::view('/users', 'pages.users.index')
    ->middleware('can:' . Permissions::VIEW_USERS())
    ->name('users.index');

// Unified Edit (Create + Edit)
Route::livewire('/users/edit/{user?}', 'pages::users.edit')
    ->name('users.edit');

// User show
Route::livewire('/users/{user}', 'pages::users.show')
    ->middleware('can:' . Permissions::VIEW_USERS())
    ->name('users.show');
