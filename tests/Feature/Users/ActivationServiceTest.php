<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Users\ActivationService;
use Database\Seeders\TenantSeeders\Production\EmailTemplateSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    asTenant();
    $this->seed(EmailTemplateSeeder::class);
    Mail::fake();
});

describe('ActivationService', function () {
    describe('createActivationToken', function () {
        it('creates a token for user with email', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);
            [$selector, $secret] = \explode('.', $token, 2);

            expect($token)->toBeString();
            expect($selector)->not()->toBeEmpty();
            expect(\strlen($secret))->toBe(64);

            // Verify token is stored in database
            $record = DB::connection('central')->table('password_reset_tokens')
                ->where('identifier', 'test@example.com')
                ->first();

            expect($record)->not()->toBeNull();
            expect($record->uuid)->toBe($selector);
            expect(Hash::check($secret, $record->token))->toBeTrue();
        });

        it('creates a token for user without email (uses username)', function () {
            $user = User::factory()->create([
                'email' => null,
                'username' => 'testuser',
            ]);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);

            expect($token)->toBeString();

            // Verify token uses username as identifier
            $record = DB::connection('central')->table('password_reset_tokens')
                ->where('identifier', 'testuser')
                ->first();

            expect($record)->not()->toBeNull();
        });

        it('replaces existing token for same user', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $token1 = $service->createActivationToken($user);
            $token2 = $service->createActivationToken($user);

            // Only one token should exist
            $count = DB::connection('central')->table('password_reset_tokens')
                ->where('identifier', 'test@example.com')
                ->count();

            expect($count)->toBe(1);
            expect($token1)->not()->toBe($token2);
        });
    });

    describe('validateToken', function () {
        it('validates a correct token', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);
            $isValid = $service->validateToken($token, 'test@example.com');

            expect($isValid)->toBeTrue();
        });

        it('rejects an incorrect token', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $service->createActivationToken($user);
            $isValid = $service->validateToken('wrong-token', 'test@example.com');

            expect($isValid)->toBeFalse();
        });

        it('rejects token for non-existent identifier', function () {
            $service = new ActivationService();

            $isValid = $service->validateToken('some-token', 'nonexistent@example.com');

            expect($isValid)->toBeFalse();
        });

        it('rejects expired token', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);

            // Manually expire the token
            DB::connection('central')->table('password_reset_tokens')
                ->where('identifier', 'test@example.com')
                ->update(['created_at' => now()->subDays(8)]);

            $isValid = $service->validateToken($token, 'test@example.com');

            expect($isValid)->toBeFalse();
        });
    });

    describe('findUserByToken', function () {
        it('finds user by valid token', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);
            $found = $service->findUserByToken($token);

            expect($found)->not()->toBeNull();
            expect($found->id)->toBe($user->id);
        });

        it('returns null for invalid token', function () {
            $service = new ActivationService();

            $found = $service->findUserByToken('invalid-token');

            expect($found)->toBeNull();
        });

        it('returns null when the selector does not match the token secret', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);
            [, $secret] = \explode('.', $token, 2);
            $invalidToken = '8b69fe9f-07b0-4a95-b638-a699ec7d7f68.' . $secret;

            $found = $service->findUserByToken($invalidToken);

            expect($found)->toBeNull();
        });

        it('returns null for expired token', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);

            // Manually expire the token
            DB::connection('central')->table('password_reset_tokens')
                ->where('identifier', 'test@example.com')
                ->update(['created_at' => now()->subDays(8)]);

            $found = $service->findUserByToken($token);

            expect($found)->toBeNull();
        });
    });

    describe('generateActivationUrl', function () {
        it('generates a valid activation URL', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);
            $service = new ActivationService();

            $url = $service->generateActivationUrl($user);

            expect($url)->toContain('/activate/');
            expect($url)->toContain('http');
        });
    });

    describe('activateWithPassword', function () {
        it('activates user and sets password', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'is_active' => false,
            ]);
            $service = new ActivationService();

            $token = $service->createActivationToken($user);
            $activated = $service->activateWithPassword($user, 'new-password-123', $token);

            expect($activated->is_active)->toBeTrue();
            expect(Hash::check('new-password-123', $activated->password))->toBeTrue();
            expect($activated->email_verified_at)->not()->toBeNull();

            // Token should be deleted
            $tokenExists = DB::connection('central')->table('password_reset_tokens')
                ->where('identifier', 'test@example.com')
                ->exists();

            expect($tokenExists)->toBeFalse();
        });

        it('rejects invalid activation token before activating user', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'is_active' => false,
            ]);
            $service = new ActivationService();

            $service->createActivationToken($user);

            expect(fn () => $service->activateWithPassword($user, 'new-password-123', 'invalid-token'))
                ->toThrow(InvalidArgumentException::class, 'The activation token is invalid or expired.');

            expect($user->fresh()->is_active)->toBeFalse();
        });
    });

    describe('getTokenExpirationDays', function () {
        it('returns the correct expiration period', function () {
            $service = new ActivationService();

            expect($service->getTokenExpirationDays())->toBe(7);
        });
    });
});
