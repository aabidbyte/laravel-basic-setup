<?php

return [
    'title' => 'Permissions',
    'matrix' => [
        'title' => 'Permission Matrix',
        'description' => 'Configure which actions can be performed on each entity.',
        'select_all_row' => 'Select all for :entity',
        'select_all_column' => 'Select all :action',
        'no_permissions' => 'No permissions available',
    ],
    'entities' => [
        'users' => 'Users',
        'roles' => 'Roles',
        'teams' => 'Teams',
        'error_logs' => 'Error Logs',
        'telescope' => 'Telescope',
        'horizon' => 'Horizon',
        'mail_settings' => 'Mail Settings',
        '{$entity}' => 'DYNAMIC_KEY: This key is dynamically constructed using PHP variables. The variable portion should be resolved to all possible values from the source class/constants. See source at app/Constants/Auth/PermissionEntity.php:72 to find the values (e.g., from a constants class). Create individual translation entries for each resolved key instead of this pattern.',
    ],
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
        '{$action}' => 'DYNAMIC_KEY: This key is dynamically constructed using PHP variables. The variable portion should be resolved to all possible values from the source class/constants. See source at app/Constants/Auth/PermissionAction.php:106 to find the values (e.g., from a constants class). Create individual translation entries for each resolved key instead of this pattern.',
    ],
];
