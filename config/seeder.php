<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seeder Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database seeders. These values are read from environment
    | variables but stored here to support config caching.
    |
    */

    'super_admin_password' => env('SUPER_ADMIN_PASSWORD', 'password'),

    'super_admin_emails' => env('SUPER_ADMIN_EMAILS', 'admin@example.com'),

    'admin_password' => env('ADMIN_PASSWORD', 'password'),
];
