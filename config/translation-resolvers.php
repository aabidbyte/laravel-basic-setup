<?php

declare(strict_types=1);

use App\Constants\Auth\PermissionAction;
use App\Constants\Auth\PermissionEntity;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Services\I18nService;

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Dynamic Key Resolvers
    |--------------------------------------------------------------------------
    |
    | This file defines automatic resolvers for dynamic translation keys.
    | When lang:sync detects a pattern defined here, it will automatically
    | execute the resolver and expand the keys.
    |
    | Format: 'pattern' => fn() => ['value1', 'value2', ...]
    |
    | Examples:
    | - 'locales.{$code}' => fn() => ['en_US', 'fr_FR']
    | - 'permissions.actions.{$action}' => fn() => PermissionAction::all()
    |
    */

    'resolvers' => [
        // Locale codes
        'locales.{$code}' => fn () => array_keys(app(I18nService::class)->getSupportedLocales()),
        'locales.{$locale}' => fn () => array_keys(app(I18nService::class)->getSupportedLocales()),
        'locales.{$activeLocale}' => fn () => array_keys(app(I18nService::class)->getSupportedLocales()),
        'locales.{$translation->locale}' => fn () => array_keys(app(I18nService::class)->getSupportedLocales()),

        // Permission actions
        'permissions.actions.{$action}' => fn () => PermissionAction::all(),

        // Permission entities
        'permissions.entities.{$entity}' => fn () => PermissionEntity::all(),

        // Email template types (enum)
        'email_templates.types.{$template->type}' => fn () => array_map(
            fn ($case) => $case->value,
            EmailTemplateType::cases(),
        ),

        // Email template status (enum)
        'email_templates.status.{$template->status}' => fn () => array_map(
            fn ($case) => $case->value,
            EmailTemplateStatus::cases(),
        ),
    ],
];
