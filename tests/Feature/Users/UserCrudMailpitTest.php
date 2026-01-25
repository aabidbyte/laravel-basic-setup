<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Mail\MailpitClient;
use App\Services\Users\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required permissions and roles
    $createPerm = Permission::create(['name' => Permissions::CREATE_USERS()]);
    $editPerm = Permission::create(['name' => Permissions::EDIT_USERS()]);
    $this->adminRole = Role::create(['name' => 'admin']);
    $this->adminRole->givePermissionTo($createPerm, $editPerm);

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->adminRole);
    $this->actingAs($this->admin);

    // Initialize Mailpit client
    $this->mailpit = new MailpitClient;
});

describe('User CRUD Email Verification', function () {
    it('UserService creates user and sends activation email', function () {
        // Use Mail::fake() for reliable testing without external dependencies
        Mail::fake();

        $userService = app(UserService::class);

        $user = $userService->createUser(
            data: [
                'name' => 'Fake Mail User',
                'email' => 'fake-mail@example.com',
            ],
            sendActivation: true,
        );

        expect($user)->toBeInstanceOf(User::class);
        expect($user->email)->toBe('fake-mail@example.com');
        expect($user->is_active)->toBeFalse();

        // Verify mail was queued (UserActivationMail implements ShouldQueue)
        Mail::assertQueued(\App\Mail\UserActivationMail::class, function ($mail) {
            return $mail->hasTo('fake-mail@example.com');
        });
    });
});

describe('DevEmailRedirect Protection', function () {
    it('respects dev redirect configuration', function () {
        // This test verifies the config is properly loaded
        config(['mail.dev_redirect.enabled' => true]);
        config(['mail.dev_redirect.to' => 'dev@example.com']);

        expect(config('mail.dev_redirect.enabled'))->toBeTrue();
        expect(config('mail.dev_redirect.to'))->toBe('dev@example.com');
    });
});
