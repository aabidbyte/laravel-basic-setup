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
    | Super Admin accounts are seeded by the production central seeders in every
    | environment. They are central-only accounts and must not be attached to
    | tenants by development seeders.
    |
    */

    'super_admin_password' => env('SUPER_ADMIN_PASSWORD', 'password'),

    'super_admin_emails' => env('SUPER_ADMIN_EMAILS', 'admin@example.com'),

    'admin_password' => env('ADMIN_PASSWORD', 'password'),
];
