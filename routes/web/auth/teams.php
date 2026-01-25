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
    ->middleware('can:' . Permissions::VIEW_TEAMS())
    ->name('teams.index');

// Unified Edit (Create + Edit)
Route::livewire('/teams/edit/{team?}', 'pages::teams.edit')
    ->name('teams.edit');

// Team show
Route::livewire('/teams/{team}', 'pages::teams.show')
    ->middleware('can:' . Permissions::VIEW_TEAMS())
    ->name('teams.show');
