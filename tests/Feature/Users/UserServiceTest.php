<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\Users\UserService;
use App\Support\Users\UserData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions
    Permission::create(['name' => Permissions::CREATE_USERS()]);
    Permission::create(['name' => Permissions::EDIT_USERS()]);

    // Create a role with permissions
    $adminRole = Role::create(['name' => 'admin']);
    $adminRole->givePermissionTo(Permissions::CREATE_USERS(), Permissions::EDIT_USERS());

    // Create a super-admin user to act as the creator
    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
    $this->actingAs($this->admin);
});

describe('UserService', function () {
    describe('createUser', function () {
        it('creates a user with basic data', function () {
            $userService = app(UserService::class);

            $user = $userService->createUser(UserData::forCreation([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'username' => 'testuser',
                'password' => 'password123',
            ]));

            expect($user)->toBeInstanceOf(User::class);
            expect($user->name)->toBe('Test User');
            expect($user->email)->toBe('test@example.com');
            expect($user->username)->toBe('testuser');
            expect($user->is_active)->toBeFalse(); // Users are inactive by default
            expect($user->created_by_user_id)->toBe($this->admin->id);
        });

        it('creates a user without email', function () {
            $userService = app(UserService::class);

            $user = $userService->createUser(UserData::forCreation([
                'name' => 'No Email User',
                'username' => 'noemail',
                'password' => 'password123',
            ]));

            expect($user)->toBeInstanceOf(User::class);
            expect($user->email)->toBeNull();
        });

        it('creates a user with timezone and locale', function () {
            $userService = app(UserService::class);

            $user = $userService->createUser(UserData::forCreation([
                'name' => 'Preference User',
                'email' => 'pref@example.com',
                'timezone' => 'Europe/Paris',
                'locale' => 'fr',
                'password' => 'password123',
            ]));

            expect($user->timezone)->toBe('Europe/Paris');
            expect($user->locale)->toBe('fr');
        });

        it('assigns roles to user', function () {
            // Create roles
            $writerRole = Role::create(['name' => 'writer']);
            $editorRole = Role::create(['name' => 'editor']);

            $userService = app(UserService::class);

            $user = $userService->createUser(UserData::forCreation(
                attributes: [
                    'name' => 'Role User',
                    'email' => 'roleuser@example.com',
                ],
                roleUuids: [$writerRole->uuid, $editorRole->uuid],
            ));

            expect($user->roles)->toHaveCount(2);
            expect($user->hasRole('writer'))->toBeTrue();
            expect($user->hasRole('editor'))->toBeTrue();
        });

        it('assigns teams to user', function () {
            $team1 = Team::factory()->create();
            $team2 = Team::factory()->create();

            $userService = app(UserService::class);

            $user = $userService->createUser(UserData::forCreation(
                attributes: [
                    'name' => 'Multi Team User',
                    'email' => 'multiteam@example.com',
                ],
                teamUuids: [$team1->uuid, $team2->uuid],
            ));

            expect($user->teams)->toHaveCount(2);
            expect($user->teams->pluck('id')->toArray())->toContain($team1->id);
            expect($user->teams->pluck('id')->toArray())->toContain($team2->id);
        });

        it('throws exception when sending activation without email', function () {
            $userService = app(UserService::class);

            expect(fn () => $userService->createUser(UserData::forCreation(
                attributes: [
                    'name' => 'No Email Activation',
                    'username' => 'noemaila',
                ],
                sendActivation: true,
            )))->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('updateUser', function () {
        it('updates user basic data', function () {
            $user = User::factory()->create(['name' => 'Original Name']);
            $userService = app(UserService::class);

            $updated = $userService->updateUser($user, UserData::forUpdate([
                'name' => 'Updated Name',
            ]));

            expect($updated->name)->toBe('Updated Name');
        });

        it('updates user timezone and locale', function () {
            $user = User::factory()->create();
            $userService = app(UserService::class);

            $updated = $userService->updateUser($user, UserData::forUpdate([
                'timezone' => 'America/New_York',
                'locale' => 'en',
            ]));

            expect($updated->timezone)->toBe('America/New_York');
            expect($updated->locale)->toBe('en');
        });

        it('updates user activation status', function () {
            $user = User::factory()->create(['is_active' => false]);
            $userService = app(UserService::class);

            $updated = $userService->updateUser($user, UserData::forUpdate([
                'is_active' => true,
            ]));

            expect($updated->is_active)->toBeTrue();
        });

        it('updates user roles', function () {
            // Create roles
            $writerRole = Role::create(['name' => 'writer']);
            $editorRole = Role::create(['name' => 'editor']);

            $user = User::factory()->create();
            $user->assignRole($writerRole);

            $userService = app(UserService::class);

            // Update to change roles from writer to editor
            $updated = $userService->updateUser(
                $user,
                UserData::forUpdate(
                    attributes: [],
                    roleUuids: [$editorRole->uuid],
                ),
            );

            $updated->refresh();
            expect($updated->roles)->toHaveCount(1);
            expect($updated->hasRole('editor'))->toBeTrue();
            expect($updated->hasRole('writer'))->toBeFalse();
        });

        it('does not update password for non-super-admin', function () {
            $user = User::factory()->create(['password' => Hash::make('old_password')]);
            $userService = app(UserService::class);

            // Admin is not super admin
            $userService->updateUser($user, UserData::forUpdate(['password' => 'new_password']));

            $user->refresh();
            expect(Hash::check('old_password', $user->password))->toBeTrue();
        });

        it('updates password for super-admin', function () {
            $user = User::factory()->create(['password' => Hash::make('old_password')]);
            $userService = app(UserService::class);

            // Assign super admin role to acting user
            $superAdminRole = Role::create(['name' => Roles::SUPER_ADMIN]);
            $this->admin->assignRole($superAdminRole);

            $userService->updateUser($user, UserData::forUpdate(['password' => 'new_password']));

            $user->refresh();
            expect(Hash::check('new_password', $user->password))->toBeTrue();
        });

        it('initiates email change flow for verified user', function () {
            Mail::fake();

            $user = User::factory()->create([
                'email' => 'old@example.com',
                'email_verified_at' => now(),
            ]);
            $userService = app(UserService::class);

            $userService->updateUser($user, UserData::forUpdate(['email' => 'new@example.com']));

            $user->refresh();
            expect($user->email)->toBe('old@example.com');
            expect($user->pending_email)->toBe('new@example.com');
            expect($user->pending_email_token)->not->toBeNull();

            Mail::assertSentCount(2);
        });

        it('directly updates email for unverified user', function () {
            Mail::fake();

            $user = User::factory()->create([
                'email' => 'old@example.com',
                'email_verified_at' => null,
            ]);
            $userService = app(UserService::class);

            $userService->updateUser($user, UserData::forUpdate(['email' => 'new@example.com']));

            $user->refresh();
            expect($user->email)->toBe('new@example.com');
            expect($user->pending_email)->toBeNull();

            Mail::assertNothingQueued();
        });
    });

    describe('activateUser', function () {
        it('activates a user', function () {
            $user = User::factory()->create(['is_active' => false]);
            $userService = app(UserService::class);

            $result = $userService->activateUser($user);

            expect($result)->toBeTrue();
            expect($user->fresh()->is_active)->toBeTrue();
        });
    });

    describe('deactivateUser', function () {
        it('deactivates a user', function () {
            $user = User::factory()->create(['is_active' => true]);
            $userService = app(UserService::class);

            $result = $userService->deactivateUser($user);

            expect($result)->toBeTrue();
            expect($user->fresh()->is_active)->toBeFalse();
        });
    });

    describe('pending email', function () {
        it('cancels pending email change', function () {
            $user = User::factory()->create([
                'pending_email' => 'new@example.com',
                'pending_email_token' => 'token',
                'pending_email_expires_at' => now()->addDays(7),
            ]);
            $userService = app(UserService::class);

            $userService->cancelPendingEmailChange($user);

            $user->refresh();
            expect($user->pending_email)->toBeNull();
            expect($user->pending_email_token)->toBeNull();
            expect($user->pending_email_expires_at)->toBeNull();
        });

        it('confirms pending email', function () {
            $user = User::factory()->create([
                'email' => 'old@example.com',
                'pending_email' => 'new@example.com',
                'pending_email_token' => 'token',
                'pending_email_expires_at' => now()->addDays(7),
            ]);

            $user->confirmPendingEmail();

            $user->refresh();
            expect($user->email)->toBe('new@example.com');
            expect($user->pending_email)->toBeNull();
            expect($user->pending_email_token)->toBeNull();
            expect($user->pending_email_expires_at)->toBeNull();
            expect($user->email_verified_at)->not->toBeNull();
        });
    });
});
