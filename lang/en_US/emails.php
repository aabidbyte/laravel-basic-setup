<?php

return [
    'user_welcome' => [
        'subject' => 'Welcome to :app_name!',
        'preheader' => 'We are excited to have you on board.',
        'greeting' => 'Hi :name,',
        'intro' => 'Thanks for signing up! We are thrilled to welcome you to our community.',
        'action' => 'Get Started',
        'closing' => 'Using our platform, you can build amazing things.',
    ],
    'password_reset' => [
        'subject' => 'Reset Password Notification',
        'intro' => 'You are receiving this email because we received a password reset request for your account.',
        'action' => 'Reset Password',
        'expiry' => 'This password reset link will expire in :count minutes.',
        'fallback' => 'If you did not request a password reset, no further action is required.',
        'preheader' => 'Reset your password to regain access.',
    ],
    'user_activated' => [
        'subject' => ':name has activated their account',
        'greeting' => 'Hello,',
        'line1' => ':name has successfully activated their account.',
        'line2' => 'You can verify their profile and permissions.',
        'action' => 'View Profile',
        'salutation' => 'Regards, :app',
        'preheader' => 'New user activation: :name',
    ],
    'verify_email' => [
        'subject' => 'Verify Email Address',
        'intro' => 'Please click the button below to verify your email address.',
        'action' => 'Verify Email Address',
        'fallback' => 'If you did not create an account, no further action is required.',
        'preheader' => 'Verify your email to get started.',
    ],
    'email_change_security' => [
        'subject' => 'Security Alert: Email Change Requested for :app',
        'greeting' => 'Hello :name,',
        'warning_title' => 'Email Change Requested',
        'intro' => 'We received a request to change the email address associated with your account.',
        'new_email' => 'New Email Address',
        'if_not_you' => 'If you did not request this change, please contact support immediately. Your account may be compromised.',
        'contact_support' => 'Contact Support:',
    ],
    'email_change_verification' => [
        'subject' => 'Verify Your New Email Address - :app',
        'greeting' => 'Hello :name,',
        'intro' => 'You have requested to change your email address. Please click the button below to verify this new address.',
        'button' => 'Verify New Email',
        'expiry' => 'This link will expire in 7 days.',
    ],
    'activation' => [
        'subject' => 'Account Activation - :app',
    ],
];
