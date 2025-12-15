<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    require __DIR__.'/web/auth/dashboard.php';
    require __DIR__.'/web/auth/settings.php';
});
