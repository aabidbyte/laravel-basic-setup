<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::livewire('/plans', 'pages::plans.⚡index')
        ->name('plans.index');

    Route::livewire('/plans/create', 'pages::plans.⚡edit')
        ->name('plans.create');

    Route::livewire('/plans/{plan}/edit', 'pages::plans.⚡edit')
        ->name('plans.edit');

    Route::livewire('/plans/{plan}', 'pages::plans.⚡show')
        ->name('plans.show');
});
