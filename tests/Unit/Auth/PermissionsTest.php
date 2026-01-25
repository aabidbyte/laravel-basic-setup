<?php

use App\Constants\Auth\PermissionAction;
use App\Constants\Auth\PermissionEntity;
use App\Constants\Auth\Permissions;
use App\Services\Auth\PermissionMatrix;

/**
 * Tests for the refactored Permissions class with dynamic permission resolution.
 *
 * These tests verify that the magic method approach correctly resolves
 * permission constants from the PermissionMatrix.
 */
test('permission magic methods return correct permission strings', function () {
    expect(Permissions::VIEW_USERS())->toBe('view users')
        ->and(Permissions::EDIT_ROLES())->toBe('edit roles')
        ->and(Permissions::DELETE_TEAMS())->toBe('delete teams')
        ->and(Permissions::VIEW_TELESCOPE())->toBe('view telescope');
});

test('all permissions from matrix are accessible via magic methods', function () {
    $matrix = new PermissionMatrix;
    $allPermissions = $matrix->getAllPermissionNames();

    foreach ($allPermissions as $permission) {
        // Convert 'view users' to 'VIEW_USERS'
        $constantName = strtoupper(str_replace(' ', '_', $permission));

        // Call the magic method
        $result = Permissions::$constantName();

        expect($result)->toBe($permission);
    }
});

test('permission methods are case sensitive', function () {
    // Correct case
    expect(Permissions::VIEW_USERS())->toBe('view users');

    // Wrong case should throw exception
    expect(fn () => Permissions::view_users())
        ->toThrow(Exception::class, 'Unknown permission constant');
});

test('throws exception for invalid permission names', function () {
    Permissions::INVALID_PERMISSION();
})->throws(Exception::class, 'Unknown permission constant');

test('permission cache works correctly', function () {
    // Clear cache first
    Permissions::clearCache();

    // First call should build cache
    $result1 = Permissions::VIEW_USERS();

    // Second call should use cache
    $result2 = Permissions::VIEW_USERS();

    expect($result1)->toBe($result2)->toBe('view users');
});

test('permissions all method returns all permission names', function () {
    $permissions = Permissions::all();
    $matrix = new PermissionMatrix;

    expect($permissions)->toBeArray()
        ->and($permissions)->toEqual($matrix->getAllPermissionNames())
        ->and($permissions)->toContain('view users')
        ->and($permissions)->toContain('edit roles')
        ->and($permissions)->toContain('delete teams');
});

test('permissions byEntity returns grouped permissions', function () {
    $byEntity = Permissions::byEntity();

    expect($byEntity)->toBeArray()
        ->and($byEntity)->toHaveKey(PermissionEntity::USERS)
        ->and($byEntity)->toHaveKey(PermissionEntity::ROLES)
        ->and($byEntity[PermissionEntity::USERS])->toContain('view users')
        ->and($byEntity[PermissionEntity::ROLES])->toContain('edit roles');
});

test('permissions forEntity returns correct permissions', function () {
    $userPermissions = Permissions::forEntity(PermissionEntity::USERS);

    expect($userPermissions)->toBeArray()
        ->and($userPermissions)->toContain('view users')
        ->and($userPermissions)->toContain('create users')
        ->and($userPermissions)->toContain('edit users')
        ->and($userPermissions)->toContain('delete users')
        ->and($userPermissions)->toContain('activate users');
});

test('permissions make generates correct permission string', function () {
    $permission = Permissions::make(PermissionEntity::USERS, PermissionAction::VIEW);

    expect($permission)->toBe('view users');
});

test('special permission names are correctly converted', function () {
    // Test underscore handling
    expect(Permissions::VIEW_ERROR_LOGS())->toBe('view error_logs')
        ->and(Permissions::FORCE_DELETE_USERS())->toBe('force_delete users')
        ->and(Permissions::GENERATE_ACTIVATION_USERS())->toBe('generate_activation users');
});

test('email template specific permissions work correctly', function () {
    expect(Permissions::VIEW_EMAIL_TEMPLATES())->toBe('view email_templates')
        ->and(Permissions::EDIT_EMAIL_TEMPLATES())->toBe('edit email_templates')
        ->and(Permissions::EDIT_BUILDER_EMAIL_TEMPLATES())->toBe('edit_builder email_templates')
        ->and(Permissions::PUBLISH_EMAIL_TEMPLATES())->toBe('publish email_templates');
});

test('system access permissions work correctly', function () {
    expect(Permissions::VIEW_TELESCOPE())->toBe('view telescope')
        ->and(Permissions::VIEW_HORIZON())->toBe('view horizon');
});

test('settings permissions work correctly', function () {
    expect(Permissions::VIEW_MAIL_SETTINGS())->toBe('view mail_settings')
        ->and(Permissions::CONFIGURE_MAIL_SETTINGS())->toBe('configure mail_settings');
});

test('clearCache resets internal state', function () {
    // Build cache
    Permissions::VIEW_USERS();

    // Clear cache
    Permissions::clearCache();

    // Should still work (will rebuild cache)
    expect(Permissions::VIEW_USERS())->toBe('view users');
});
