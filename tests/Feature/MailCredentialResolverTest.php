<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\MailSettings;
use App\Models\Team;
use App\Models\User;
use App\Services\Mail\MailCredentialResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear permission cache before each test
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Set default team_id for tests (teams are enabled by default)
    setPermissionsTeamId(1);

    // Create permissions used in tests
    \App\Models\Permission::create(['name' => Permissions::CONFIGURE_MAIL_SETTINGS]);
});

describe('MailCredentialResolver', function () {
    describe('resolve', function () {
        it('returns null when no custom settings exist (uses environment)', function () {
            $resolver = new MailCredentialResolver;

            $settings = $resolver->resolve();

            expect($settings)->toBeNull();
        });

        it('returns user settings when user has permission and settings', function () {
            $user = User::factory()->create();
            $user->givePermissionTo(Permissions::CONFIGURE_MAIL_SETTINGS);

            $mailSettings = MailSettings::create([
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'provider' => 'smtp',
                'host' => 'smtp.user.com',
                'port' => 587,
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;
            $settings = $resolver->resolve($user);

            expect($settings)->not()->toBeNull();
            expect($settings->host)->toBe('smtp.user.com');
        });

        it('skips user settings when user lacks permission', function () {
            $user = User::factory()->create();
            // User does NOT have CONFIGURE_MAIL_SETTINGS permission

            $mailSettings = MailSettings::create([
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'provider' => 'smtp',
                'host' => 'smtp.user.com',
                'port' => 587,
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;
            $settings = $resolver->resolve($user);

            expect($settings)->toBeNull();
        });

        it('returns team settings when available', function () {
            $team = Team::factory()->create();
            $user = User::factory()->create(['team_id' => $team->id]);

            $mailSettings = MailSettings::create([
                'settable_type' => Team::class,
                'settable_id' => $team->id,
                'provider' => 'smtp',
                'host' => 'smtp.team.com',
                'port' => 587,
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;
            $settings = $resolver->resolve($user);

            expect($settings)->not()->toBeNull();
            expect($settings->host)->toBe('smtp.team.com');
        });

        it('returns app settings when no user or team settings', function () {
            $mailSettings = MailSettings::create([
                'settable_type' => 'app',
                'settable_id' => null,
                'provider' => 'smtp',
                'host' => 'smtp.app.com',
                'port' => 587,
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;
            $settings = $resolver->resolve();

            expect($settings)->not()->toBeNull();
            expect($settings->host)->toBe('smtp.app.com');
        });

        it('respects hierarchy: user > team > app', function () {
            $team = Team::factory()->create();
            $user = User::factory()->create(['team_id' => $team->id]);
            $user->givePermissionTo(Permissions::CONFIGURE_MAIL_SETTINGS);

            // Create app settings
            MailSettings::create([
                'settable_type' => 'app',
                'settable_id' => null,
                'provider' => 'smtp',
                'host' => 'smtp.app.com',
                'is_active' => true,
            ]);

            // Create team settings
            MailSettings::create([
                'settable_type' => Team::class,
                'settable_id' => $team->id,
                'provider' => 'smtp',
                'host' => 'smtp.team.com',
                'is_active' => true,
            ]);

            // Create user settings
            MailSettings::create([
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'provider' => 'smtp',
                'host' => 'smtp.user.com',
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;
            $settings = $resolver->resolve($user, $team);

            // Should get user settings (highest priority)
            expect($settings->host)->toBe('smtp.user.com');
        });

        it('falls back to team when user settings inactive', function () {
            $team = Team::factory()->create();
            $user = User::factory()->create(['team_id' => $team->id]);
            $user->givePermissionTo(Permissions::CONFIGURE_MAIL_SETTINGS);

            // Create team settings (active)
            MailSettings::create([
                'settable_type' => Team::class,
                'settable_id' => $team->id,
                'provider' => 'smtp',
                'host' => 'smtp.team.com',
                'is_active' => true,
            ]);

            // Create user settings (inactive)
            MailSettings::create([
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'provider' => 'smtp',
                'host' => 'smtp.user.com',
                'is_active' => false,
            ]);

            $resolver = new MailCredentialResolver;
            $settings = $resolver->resolve($user, $team);

            // Should fall back to team settings
            expect($settings->host)->toBe('smtp.team.com');
        });
    });

    describe('getSettingsSource', function () {
        it('returns environment when no custom settings', function () {
            $resolver = new MailCredentialResolver;

            expect($resolver->getSettingsSource())->toBe('environment');
        });

        it('returns user when user settings used', function () {
            $user = User::factory()->create();
            $user->givePermissionTo(Permissions::CONFIGURE_MAIL_SETTINGS);

            MailSettings::create([
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'provider' => 'smtp',
                'host' => 'smtp.user.com',
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;

            expect($resolver->getSettingsSource($user))->toBe('user');
        });

        it('returns team when team settings used', function () {
            $team = Team::factory()->create();
            $user = User::factory()->create(['team_id' => $team->id]);

            MailSettings::create([
                'settable_type' => Team::class,
                'settable_id' => $team->id,
                'provider' => 'smtp',
                'host' => 'smtp.team.com',
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;

            expect($resolver->getSettingsSource($user))->toBe('team');
        });

        it('returns app when app settings used', function () {
            MailSettings::create([
                'settable_type' => 'app',
                'settable_id' => null,
                'provider' => 'smtp',
                'host' => 'smtp.app.com',
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;

            expect($resolver->getSettingsSource())->toBe('app');
        });
    });

    describe('hasCustomSettings', function () {
        it('returns false when no custom settings', function () {
            $resolver = new MailCredentialResolver;

            expect($resolver->hasCustomSettings())->toBeFalse();
        });

        it('returns true when settings exist', function () {
            MailSettings::create([
                'settable_type' => 'app',
                'settable_id' => null,
                'provider' => 'smtp',
                'host' => 'smtp.app.com',
                'is_active' => true,
            ]);

            $resolver = new MailCredentialResolver;

            expect($resolver->hasCustomSettings())->toBeTrue();
        });
    });
});
