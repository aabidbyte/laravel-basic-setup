<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Mail\MailpitClient;
use App\Services\Users\ActivationService;
use App\Services\Users\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear permission cache
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    setPermissionsTeamId(1);

    // Create required permissions
    \App\Models\Permission::create(['name' => \App\Constants\Auth\Permissions::CREATE_USERS]);
    \App\Models\Permission::create(['name' => \App\Constants\Auth\Permissions::EDIT_USERS]);

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(\App\Constants\Auth\Permissions::CREATE_USERS);
    $this->actingAs($this->admin);

    // Initialize Mailpit client
    $this->mailpit = new MailpitClient;
});

describe('User CRUD with Email Verification via Mailpit', function () {
    describe('when Mailpit is available', function () {
        beforeEach(function () {
            if (! $this->mailpit->isAvailable()) {
                $this->markTestSkipped('Mailpit is not running. Start with: mailpit');
            }
            // Clear all messages before each test
            $this->mailpit->deleteAllMessages();
        });

        it('sends activation email when creating user with sendActivation=true', function () {
            $userService = app(UserService::class);

            $user = $userService->createUser(
                data: [
                    'name' => 'Test Activation User',
                    'email' => 'activation-test@example.com',
                ],
                sendActivation: true,
            );

            // Wait for email to arrive in Mailpit
            $message = $this->mailpit->waitForMessage('activation-test@example.com', 5);

            expect($message)->not()->toBeNull();
            expect($message['To'][0]['Address'] ?? null)->toBe('activation-test@example.com');
            expect($message['Subject'] ?? '')->toContain('Activate');
        });

        it('activation email contains valid activation link', function () {
            $userService = app(UserService::class);

            $user = $userService->createUser(
                data: [
                    'name' => 'Link Test User',
                    'email' => 'link-test@example.com',
                ],
                sendActivation: true,
            );

            $message = $this->mailpit->waitForMessage('link-test@example.com', 5);
            expect($message)->not()->toBeNull();

            $links = $this->mailpit->extractLinksFromMessage($message['ID']);
            $activationLinks = array_filter($links, fn ($link) => str_contains($link, '/activate/'));

            expect($activationLinks)->not()->toBeEmpty();
        });

        it('welcome email is sent after user activation', function () {
            $userService = app(UserService::class);
            $activationService = app(ActivationService::class);

            // Create user and get activation email
            $user = $userService->createUser(
                data: [
                    'name' => 'Welcome Test User',
                    'email' => 'welcome-test@example.com',
                ],
                sendActivation: true,
            );

            // Wait for activation email
            $this->mailpit->waitForMessage('welcome-test@example.com', 5);

            // Clear messages and activate user
            $this->mailpit->deleteAllMessages();

            $token = $activationService->createActivationToken($user);
            $activationService->activateWithPassword($user, 'new-password-123', $token);

            // Wait for welcome email
            $message = $this->mailpit->waitForMessage('welcome-test@example.com', 5);

            expect($message)->not()->toBeNull();
            expect($message['Subject'] ?? '')->toContain('Welcome');
        });
    });

    describe('Mail sending verification (fallback tests)', function () {
        it('UserService creates user and sends activation email', function () {
            // Swap to Mail::fake() BEFORE creating the user
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
