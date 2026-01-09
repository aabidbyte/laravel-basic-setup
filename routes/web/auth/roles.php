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
    ->middleware('can:' . Permissions::VIEW_ROLES)
    ->name('roles.index');

// Role create
Route::livewire('/roles/create', 'pages::roles.create')
    ->middleware('can:' . Permissions::CREATE_ROLES)
    ->name('roles.create');

// Role show
Route::livewire('/roles/{role}', 'pages::roles.show')
    ->middleware('can:' . Permissions::VIEW_ROLES)
    ->name('roles.show');

// Role edit
Route::livewire('/roles/{role}/edit', 'pages::roles.edit')
    ->middleware('can:' . Permissions::EDIT_ROLES)
    ->name('roles.edit');
