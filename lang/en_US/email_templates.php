<?php

return [
    'kind' => [
        'layout' => 'Layout',
        'content' => 'Content',
    ],
    'types' => [
        'transactional' => 'Transactional',
        'marketing' => 'Marketing',
        'system' => 'System',
    ],
    'status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],
    'actions' => [
        'publish' => 'Publish',
        'archive' => 'Archive',
    ],
    'form' => [
        'basic_info' => 'Basic Information',
        'settings' => 'Settings',
        'name' => 'Name',
        'description' => 'Description',
        'type' => 'Type',
        'status' => 'Status',
        'layout' => 'Layout',
        'subject' => 'Subject',
        'preheader' => 'Preheader',
        'html_content' => 'HTML Content',
        'text_content' => 'Text Content',
        'is_default' => 'Default Layout',
    ],
    'show' => [
        'basic_info' => 'Basic Information',
        'translations' => 'Translations',
        'html_length' => 'HTML Length',
    ],
    'merge_tags' => [
        'insert' => 'Insert Merge Tag',
        'search' => 'Search tags...',
        'context' => 'Context Variables',
        'no_tags' => 'No tags available for this template.',
        'help' => 'Click a tag to insert. Use {{ tag }} for escaped content or {{{ tag }}} for raw HTML.',
    ],
    'preview' => [
        'button' => 'Preview',
        'title' => 'Email Preview',
        'subject' => 'Subject',
        'error' => 'Unable to generate preview',
    ],
    'cannot_delete_system' => 'Cannot delete system template.',
    'cannot_delete_default' => 'Cannot delete default layout.',
];
