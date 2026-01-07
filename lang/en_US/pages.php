<?php

return [
    'dashboard' => 'Dashboard',
    'notifications' => 'Notifications',
    'common' => [
        'index' => [
            'title' => ':type',
            'description' => 'Manage and view all :type_plural in the system',
        ],
        'show' => [
            'title' => ':name - :type Details',
            'description' => 'View :type information',
            'subtitle' => ':type details and management',
        ],
        'create' => [
            'title' => 'Create New :type',
            'description' => 'Add a new :type to the system',
            'submit' => 'Create :type',
            'success' => ':name has been created successfully',
            'error' => 'Failed to create :type',
        ],
        'edit' => [
            'title' => 'Edit :type',
            'description' => 'Update :type information',
            'submit' => 'Save Changes',
            'success' => ':name has been updated successfully',
            'error' => 'Failed to update :type',
        ],
        'messages' => [
            'deleted' => ':name deleted successfully',
            'activated' => ':name activated successfully',
            'deactivated' => ':name deactivated successfully',
        ],
    ],
    'users' => [
        'index' => 'Users',
        'create' => 'Create New User',
        'edit' => 'Edit User',
        'show' => 'User Details',
        'description' => 'Manage and view all users in the system',
    ],
    'settings' => [
        'profile' => 'Profile Settings',
        'password' => 'Password Settings',
        'two_factor' => 'Two-Factor Authentication',
    ],
];
