<?php

use Illuminate\Support\Facades\Route;

// Dashboard routes
Route::livewire('/', 'pages::dashboard')
    ->name('home');

Route::livewire('dashboard', 'pages::dashboard')
    ->name('dashboard');
