<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vite Dev Server Configuration
    |--------------------------------------------------------------------------
    |
    | These values configure the Vite development server connection.
    | Used by CSP preset to allow Vite's dev server in local environment.
    |
    */

    'dev_server' => [
        'host' => env('VITE_DEV_SERVER_HOST', '127.0.0.1'),
        'port' => env('VITE_DEV_SERVER_PORT', '5173'),
    ],
];
