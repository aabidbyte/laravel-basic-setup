<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$config = config('database.connections.landlord');
$config['database'] = null;
Illuminate\Support\Facades\Config::set('database.connections.temp', $config);
print_r(Illuminate\Support\Facades\DB::connection('temp')->select('SHOW DATABASES'));
