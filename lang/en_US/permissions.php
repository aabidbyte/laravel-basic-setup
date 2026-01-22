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
        'email_templates' => 'Email Templates',
        'email_layouts' => 'Email Layouts',
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
    ],
];
