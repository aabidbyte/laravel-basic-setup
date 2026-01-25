<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;

/**
 * Role Management Routes
 *
 * CRUD routes for role management.
 * All routes require authentication and appropriate permissions.
 */

// Roles list
Route::view('/roles', 'pages.roles.index')
    ->middleware('can:' . Permissions::VIEW_ROLES())
    ->name('roles.index');

Route::livewire('/roles/edit/{role?}', 'pages::roles.edit')
    ->name('roles.edit');

// Role show
Route::livewire('/roles/{role}', 'pages::roles.show')
    ->middleware('can:' . Permissions::VIEW_ROLES())
    ->name('roles.show');
