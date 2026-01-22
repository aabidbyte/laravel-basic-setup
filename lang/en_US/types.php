<?php

return [
    'user' => 'User',
    'users' => 'Users',
    'role' => 'Role',
    'roles' => 'Roles',
    'team' => 'Team',
    'teams' => 'Teams',
    'error_log' => 'Error Log',
    'error_logs' => 'Error Logs',
    'email_template' => 'Email Template',
    'email_templates' => 'Email Templates',
    'email_layout' => 'Email Layout',
    'email_content' => 'Email Content',
    'email_layouts' => 'Email Layouts',
    'email_contents' => 'Email Contents',
    '$type' => 'DYNAMIC_KEY: This key was not found in config/translation-resolvers.php. To auto-resolve this dynamic pattern:

1. Open config/translation-resolvers.php
2. Add this pattern with a resolver:
   \'types.$type\' => fn() => YourClass::getValues(),
3. Run: php artisan lang:sync --write

Example resolvers:
- Static method: fn() => PermissionAction::all()
- Service: fn() => array_keys(app(I18nService::class)->getSupportedLocales())
- Database: fn() => DB::table(\'x\')->pluck(\'column\')->toArray()
- Enum: fn() => array_map(fn($c) => $c->value, Status::cases())

Source: app/Services/EmailTemplate/EntityTypeRegistry.php:77',
    '$entityType' => 'DYNAMIC_KEY: This key was not found in config/translation-resolvers.php. To auto-resolve this dynamic pattern:

1. Open config/translation-resolvers.php
2. Add this pattern with a resolver:
   \'types.$entityType\' => fn() => YourClass::getValues(),
3. Run: php artisan lang:sync --write

Example resolvers:
- Static method: fn() => PermissionAction::all()
- Service: fn() => array_keys(app(I18nService::class)->getSupportedLocales())
- Database: fn() => DB::table(\'x\')->pluck(\'column\')->toArray()
- Enum: fn() => array_map(fn($c) => $c->value, Status::cases())

Source: resources/views/components/ui/merge-tag-picker.blade.php:20',
];
