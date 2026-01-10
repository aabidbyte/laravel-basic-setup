<?php

return [
    'activation' => [
        'subject' => 'Activate your :app account',
        'greeting' => 'Hello :name,',
        'intro' => 'An account has been created for you. Please click the button below to set your password and activate your account.',
        'instructions' => 'After setting your password, you will be able to log in and start using the application.',
        'button' => 'Activate Account',
        'expires' => 'This activation link will expire in :days days.',
        'link_fallback' => 'If you have trouble clicking the button, copy and paste the link below into your browser:',
        'footer' => 'If you did not expect to receive this email, you can safely ignore it.',
    ],
    'welcome' => [
        'subject' => 'Welcome to :app!',
        'greeting' => 'Welcome, :name!',
        'intro' => 'We\'re excited to have you on board.',
        'ready' => 'Your account is now active and ready to use.',
        'button' => 'Log In',
        'help' => 'If you have any questions, feel free to reply to this email.',
        'footer' => 'The :app Team',
    ],
    'email_change_security' => [
        'subject' => 'Security Alert: Email Change Request - :app',
        'greeting' => 'Hello :name,',
        'warning_title' => 'Important Security Notification',
        'intro' => 'We received a request to change the email address associated with your account.',
        'new_email' => 'New Email Request',
        'if_not_you' => 'If you did not initiate this change, your account may be compromised.',
        'contact_support' => 'Please contact our support team immediately:',
        'footer' => 'This is an automated security notification.',
    ],
    'email_change_verification' => [
        'subject' => 'Verify your new email address - :app',
        'greeting' => 'Hello :name,',
        'intro' => 'You requested to change your email address. To confirm this change, please verify your new email address.',
        'instructions' => 'Click the button below to verify this email address and complete the update.',
        'button' => 'Verify Email Change',
        'expires' => 'This link will expire in :days days.',
        'link_fallback' => 'If you have trouble clicking the button, copy and paste the link below into your browser:',
        'footer' => 'If you did not request this change, you can safely ignore this email.',
    ],
];
