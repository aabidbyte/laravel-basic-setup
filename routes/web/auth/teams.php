<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;

/**
 * Team Management Routes
 *
 * CRUD routes for team management.
 * All routes require authentication and appropriate permissions.
 */

// Teams list
Route::view('/teams', 'pages.teams.index')
    ->middleware('can:' . Permissions::VIEW_TEAMS)
    ->name('teams.index');

// Team create
Route::livewire('/teams/create', 'pages::teams.create')
    ->middleware('can:' . Permissions::CREATE_TEAMS)
    ->name('teams.create');

// Team show
Route::livewire('/teams/{team}', 'pages::teams.show')
    ->middleware('can:' . Permissions::VIEW_TEAMS)
    ->name('teams.show');

// Team edit
Route::livewire('/teams/{team}/edit', 'pages::teams.edit')
    ->middleware('can:' . Permissions::EDIT_TEAMS)
    ->name('teams.edit');
