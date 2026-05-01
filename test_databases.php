<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$config = config('database.connections.landlord');
$config['database'] = null;
Config::set('database.connections.temp', $config);
print_r(DB::connection('temp')->select('SHOW DATABASES'));
