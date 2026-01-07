<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Super Team ID
    |--------------------------------------------------------------------------
    |
    | The team ID that bypasses team scope filtering. Users in this team
    | can see all data regardless of team assignment. Typically this is
    | the "Default Team" or "Admin Team".
    |
    */
    'super_team_id' => env('SUPER_TEAM_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Super Team Name
    |--------------------------------------------------------------------------
    |
    | The name used for the super team in seeders and documentation.
    |
    */
    'super_team_name' => env('SUPER_TEAM_NAME', 'Default Team'),
];
