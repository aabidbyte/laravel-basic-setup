<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule notification pruning daily
Schedule::command('notifications:prune-read')->daily()->withoutOverlapping()->onOneServer();

Schedule::command('errors:prune')->daily()->withoutOverlapping()->onOneServer();

// Schedule Horizon snapshots every five minutes
Schedule::command('horizon:snapshot')->everyFiveMinutes()->withoutOverlapping()->onOneServer();

// Schedule Telescope pruning daily
if (class_exists('Laravel\Telescope\Telescope')) {
    Schedule::command('telescope:prune')->daily()->withoutOverlapping()->onOneServer();
}
