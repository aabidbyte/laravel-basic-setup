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

    // Error management views
    'management' => [
        'title' => 'Error Logs',
        'description' => 'View and manage application errors',
        'empty' => 'No errors found',
        'empty_description' => 'No errors have been recorded yet.',

        // Table columns
        'reference_id' => 'Reference ID',
        'exception' => 'Exception',
        'message' => 'Message',
        'url' => 'URL',
        'user' => 'User',
        'status' => 'Status',
        'created_at' => 'Created',
        'guest' => 'Guest',

        // Status badges
        'resolved' => 'Resolved',
        'unresolved' => 'Unresolved',

        // Filters
        'all_status' => 'All Status',
        'all_exceptions' => 'All Exception Types',
        'date_range' => 'Date Range',
        'today' => 'Today',
        'last_7_days' => 'Last 7 days',
        'last_30_days' => 'Last 30 days',

        // Detail view
        'exception_info' => 'Exception Info',
        'request_info' => 'Request Info',
        'context' => 'Context',
        'stack_trace' => 'Stack Trace',
        'resolution' => 'Resolution',
        'resolved_at' => 'Resolved at',
        'resolved_by' => 'Resolved by',
        'resolution_notes' => 'Notes',
        'file_line' => 'File',
        'ip_address' => 'IP Address',
        'user_agent' => 'User Agent',
        'method' => 'Method',
        'no_context' => 'No context available',
        'no_stack_trace' => 'No stack trace available',

        // Resolve modal
        'resolve_title' => 'Resolve Error',
        'resolve_notes_placeholder' => 'What fixed this error? (optional)',
        'resolve_confirm' => 'Mark as Resolved',
        'resolve_success' => 'Error marked as resolved',

        // Actions
        'resolve_selected' => 'Resolve',
        'delete_selected' => 'Delete',
        'confirm_resolve' => 'Mark this error as resolved?',
        'confirm_bulk_resolve' => 'Mark all these errors as resolved?',
        'confirm_delete' => 'Delete this error? This cannot be undone.',
        'confirm_bulk_delete' => 'Delete all these errors? This cannot be undone.',
        'deleted_successfully' => 'Error deleted successfully',
        'bulk_resolved_successfully' => ':count errors marked as resolved',
        'bulk_deleted_successfully' => ':count errors deleted',
    ],
];
