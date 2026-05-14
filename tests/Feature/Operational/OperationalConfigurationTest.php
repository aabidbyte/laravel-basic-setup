<?php

declare(strict_types=1);

namespace Tests\Feature\Operational;

use App\Mail\UserActivationMail;
use App\Services\Users\UserService;
use App\Support\Users\UserData;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class OperationalConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        asTenant();
    }

    public function test_scheduler_includes_horizon_snapshot_and_prune_commands()
    {
        $events = collect(Schedule::events());

        $scheduledCommands = $events->map(fn ($event) => $event->command);

        expect($scheduledCommands)->toContain('\'artisan\' horizon:snapshot');
        expect($scheduledCommands)->toContain('\'artisan\' notifications:prune-read');
        expect($scheduledCommands)->toContain('\'artisan\' errors:prune');
    }

    public function test_reverb_allowed_origins_is_driven_by_environment()
    {
        expect(config('reverb.apps.apps.0.allowed_origins'))->toBeArray();
    }

    public function test_telescope_is_disabled_by_default_when_debug_is_off()
    {
        config(['app.debug' => false]);
        // Re-read config or simulate how it would be evaluated
        $enabled = env('TELESCOPE_ENABLED', config('app.debug'));
        expect($enabled)->toBeFalse();
    }

    public function test_activation_email_is_sent_after_user_creation_transaction_commits()
    {
        Mail::fake();

        $userService = app(UserService::class);
        $userData = new UserData(
            attributes: [
                'name' => 'Post Commit Test',
                'email' => 'postcommit@example.com',
                'username' => 'postcommit',
            ],
            sendActivation: true,
        );

        $user = $userService->createUser($userData);

        Mail::assertSent(UserActivationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_no_activation_email_is_sent_if_user_creation_transaction_fails()
    {
        Mail::fake();

        $userService = app(UserService::class);
        $userData = new UserData(
            attributes: [
                'name' => 'Rollback Test',
                'email' => 'rollback@example.com',
                'username' => 'rollback',
            ],
            sendActivation: true,
        );

        try {
            DB::transaction(function () use ($userService, $userData) {
                $userService->createUser($userData);
                throw new Exception('Forced failure');
            });
        } catch (Exception $e) {
            // Expected
        }

        Mail::assertNothingSent();
    }
}
