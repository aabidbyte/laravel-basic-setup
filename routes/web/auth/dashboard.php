<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;

// Dashboard routes
Route::livewire('/', 'pages::dashboard')
    ->name('dashboard');

// Notification center
Route::livewire('/notifications', 'pages::notifications.index')
    ->name('notifications.index');

// Users list
Route::view('/users', 'pages.users.index')
    ->middleware('can:' . Permissions::VIEW_USERS)
    ->name('users.index');

// User create
Route::livewire('/users/create', 'pages::users.create')
    ->middleware('can:' . Permissions::CREATE_USERS)
    ->name('users.create');

// User show
Route::livewire('/users/{user}', 'pages::users.show')
    ->middleware('can:' . Permissions::VIEW_USERS)
    ->name('users.show');

// User edit
Route::livewire('/users/{user}/edit', 'pages::users.edit')
    ->middleware('can:' . Permissions::EDIT_USERS)
    ->name('users.edit');
