<?php

/**
 * Error handling translations.
 *
 * Messages displayed to users during error scenarios.
 * Reference IDs help support teams locate specific errors.
 */
return [
    // Page titles
    'page_title' => 'Error',
    'page_subtitle' => 'Something went wrong',

    // Generic error messages
    'oops' => 'Oops! Something went wrong',
    'generic_title' => 'An error occurred',
    'generic_message' => 'We encountered an unexpected issue. Our team has been notified and is working to fix it.',
    'reference' => 'Reference: :id',

    // Specific error types
    'validation_failed' => 'Validation failed',
    'unauthorized' => 'Unauthorized',
    'please_login' => 'Please log in to continue',
    'not_found' => 'Not found',
    'resource_not_found' => 'The requested resource was not found',
    'forbidden' => 'Access denied',
    'forbidden_message' => 'You do not have permission to access this resource',

    // Development mode
    'dev_mode' => 'Development Mode',
    'dev_message' => 'Full error details are shown below for debugging purposes.',

    // Error page
    'go_back' => 'Go back',
    'go_home' => 'Go to dashboard',
    'try_again' => 'Try again',
    'contact_support' => 'If this problem persists, please contact support with the reference ID above.',

    // HTTP status codes
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '403' => 'Forbidden',
    '404' => 'Page Not Found',
    '405' => 'Method Not Allowed',
    '419' => 'Page Expired',
    '422' => 'Unprocessable Entity',
    '429' => 'Too Many Requests',
    '500' => 'Server Error',
    '502' => 'Bad Gateway',
    '503' => 'Service Unavailable',
    '504' => 'Gateway Timeout',
];
