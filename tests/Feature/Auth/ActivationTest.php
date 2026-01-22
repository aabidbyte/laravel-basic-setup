<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Users\ActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivationTest extends TestCase
{
    use RefreshDatabase;

    protected ActivationService $activationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\EmailTemplateSeeder::class);
        $this->activationService = app(ActivationService::class);
    }

    public function test_can_view_activation_page_with_valid_token()
    {
        $user = User::factory()->create([
            'password' => null, // Simulate user created without password
        ]);

        $token = $this->activationService->createActivationToken($user);

        $response = $this->get(route('auth.activate', $token));

        $response->assertStatus(200);
        $response->assertViewIs('pages.auth.activate');
        $response->assertViewHas('tokenValid', true);
        $response->assertSee($user->name);
    }

    public function test_view_activation_page_with_invalid_token_shows_error()
    {
        $response = $this->get(route('auth.activate', 'invalid-token'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.auth.activate');
        $response->assertViewHas('tokenValid', false);
        $response->assertSee(__('authentication.activation.invalid_title'));
    }

    public function test_can_activate_account_with_valid_token_and_password()
    {
        $user = User::factory()->create([
            'password' => null,
            'is_active' => false,
        ]);

        $token = $this->activationService->createActivationToken($user);

        $response = $this->post(route('auth.activate', $token), [
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('activated', true);

        $this->assertTrue($user->fresh()->is_active);
        $this->assertTrue(Hash::check('Password123!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'identifier' => $user->email,
        ]);
    }

    public function test_cannot_activate_with_invalid_token()
    {
        $user = User::factory()->create([
            'password' => null,
            'is_active' => false,
        ]);

        $response = $this->post(route('auth.activate', 'invalid-token'), [
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect();
        // Should flash error
        // Note: NotificationBuilder uses session flash or toasts, might need specific assertion if we want to be strict.
        // For now, assert user is not activated.
        $this->assertFalse($user->fresh()->is_active);
        $this->assertNull($user->fresh()->password);
    }

    public function test_activation_requires_valid_password()
    {
        $user = User::factory()->create([
            'password' => null,
            'is_active' => false,
        ]);

        $token = $this->activationService->createActivationToken($user);

        $response = $this->post(route('auth.activate', $token), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_activation_requires_password_confirmation()
    {
        $user = User::factory()->create([
            'password' => null,
            'is_active' => false,
        ]);

        $token = $this->activationService->createActivationToken($user);

        $response = $this->post(route('auth.activate', $token), [
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertFalse($user->fresh()->is_active);
    }
}
