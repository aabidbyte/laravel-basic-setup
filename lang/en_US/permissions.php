<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for permission labels and
    | descriptions in the permission matrix UI.
    |
    */

    'title' => 'Permissions',
    'matrix' => [
        'title' => 'Permission Matrix',
        'description' => 'Configure which actions can be performed on each entity.',
        'select_all_row' => 'Select all for :entity',
        'select_all_column' => 'Select all :action',
        'no_permissions' => 'No permissions available',
    ],

    // Entity labels
    'entities' => [
        'users' => 'Users',
        'roles' => 'Roles',
        'teams' => 'Teams',
        'error_logs' => 'Error Logs',
        'telescope' => 'Telescope',
        'horizon' => 'Horizon',
        'mail_settings' => 'Mail Settings',
    ],

    // Action labels
    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'force_delete' => 'Force Delete',
        'export' => 'Export',
        'publish' => 'Publish',
        'unpublish' => 'Unpublish',
        'resolve' => 'Resolve',
        'activate' => 'Activate',
        'configure' => 'Configure',
        'generate_activation' => 'Generate Activation',
    ],
];
