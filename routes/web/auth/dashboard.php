<?php

use App\Constants\Permissions;
use Illuminate\Support\Facades\Route;

// Dashboard routes
Route::livewire('/', 'pages::dashboard')
    ->name('dashboard');

// Notification center
Route::livewire('/notifications', 'pages::notifications.index')
    ->name('notifications.index');

// Users list
Route::view('/users', 'pages.users.index')
    ->middleware('can:'.Permissions::VIEW_USERS)
    ->name('users.index');
