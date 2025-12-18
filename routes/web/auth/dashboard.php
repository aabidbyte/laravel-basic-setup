<?php

use Illuminate\Support\Facades\Route;

// Dashboard routes
Route::livewire('/', 'pages::dashboard')
    ->name('dashboard');

// Notification center
Route::livewire('/notifications', 'pages::notifications.index')
    ->name('notifications.index');
