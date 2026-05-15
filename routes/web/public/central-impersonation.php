<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\CentralImpersonationController;
use Illuminate\Support\Facades\Route;

Route::get('/central/impersonate/{user}', CentralImpersonationController::class)
    ->middleware(['signed:relative', 'throttle:6,1'])
    ->name('central.impersonate');
