<?php

use Illuminate\Support\Facades\Route;

// Dashboard routes
Route::livewire('/', 'pages::dashboard')
    ->name('dashboard');
