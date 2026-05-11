<?php

return [
    // System messages, notifications, alerts
    // Use semantic keys: messages.{category}.{type}

    // Auth
    'auth' => [
        'password_reset' => 'Your password has been reset.',
        'password_reset_link_sent' => 'We have emailed your password reset link.',
    ],

    // Preferences
    'preferences' => [
        'invalid_theme' => 'Invalid theme selected.',
        'theme_updated' => 'Theme updated successfully.',
        'invalid_locale' => 'Invalid locale selected.',
        'locale_updated' => 'Language updated successfully.',
    ],

    // Notifications
    'notifications' => [
        'user_activated' => [
            'subject' => 'New User Activation',
            'greeting' => 'Hello!',
            'line1' => ':name has just activated their account.',
            'line2' => 'You can now view their profile and manage their permissions.',
            'action' => 'View User Profile',
            'salutation' => 'Regards,',
            'title' => 'User Activated',
            'subtitle' => 'New user activation',
            'content' => ':name (:email) has activated their account.',
            'toast_title' => 'User Activated',
            'toast_subtitle' => ':name has activated their account.',
        ],
    ],

    // Common
    'common' => [
        'no_email' => 'No email address',
    ],

    // Database
    'database' => [
        'setup' => [
            'master_ready' => 'Master Database Ready',
            'tenant_ready' => 'Tenant Database Ready',
            'success_content' => "The :type database ':db' has been successfully set up.",
            'failure_title' => 'Database Setup Failed',
            'failure_content' => "Failed to set up :type database ':db': :error",
        ],
    ],

    // Context
    'context' => [
        'impersonating' => 'You are currently logged in as :name.',
    ],
];
