<?php

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('reset password link can be requested', function () {
    Notification::fake();

    // Seed/Update the Password Reset template required by the notification
    $template = EmailTemplate::updateOrCreate(
        ['name' => 'Password Reset'],
        [
            'is_layout' => false,
            'status' => EmailTemplateStatus::PUBLISHED,
            'type' => EmailTemplateType::SYSTEM,
        ],
    );

    $template->translations()->updateOrCreate(
        ['locale' => 'en_US'],
        [
            'subject' => 'Reset Password',
            'html_content' => 'Reset Link: {{ action.reset_url }}',
        ],
    );

    $user = User::factory()->create();

    $response = $this->post(route('password.email'), [
        'identifier' => $user->email,
    ]);

    $response->assertSessionHasNoErrors();
    Notification::assertSentTo($user, \App\Notifications\Auth\ResetPasswordNotification::class, function ($notification, $channels, $notifiable) use ($user) {
        $mailData = $notification->toMail($notifiable);
        $rendered = $mailData->render();

        return str_contains($rendered, 'identifier=' . urlencode($user->email));
    });
});

test('reset password link can be requested with username', function () {
    Notification::fake();

    $user = User::factory()->create(['username' => 'johndoe']);

    $response = $this->post(route('password.email'), [
        'identifier' => 'johndoe',
    ]);

    $response->assertSessionHasNoErrors();
    Notification::assertSentTo($user, \App\Notifications\Auth\ResetPasswordNotification::class);
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post(route('password.email'), ['identifier' => $user->email]);

    $response->assertSessionHasNoErrors();
    Notification::assertSentTo($user, \App\Notifications\Auth\ResetPasswordNotification::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'identifier' => $user->email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});

test('password cannot be reset with expired token', function () {
    Notification::fake();

    $user = User::factory()->create();

    // Generate token
    $token = Password::broker()->createToken($user);

    // Fast forward 61 minutes
    $this->travel(61)->minutes();

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'identifier' => $user->email,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    // Note: The error key here depends on what the backed validation throws.
    // If we map identifier -> email using middleware, validation might still complain about 'email'
    // if the token is invalid for that email?
    // Actually, `ResetUserPassword` validates `email`.
    // Our middleware adds `email` to the request request.
    // So error will likely be on `email`.
    // However, if we want to be strict, we might want to map errors back?
    // For now, let's assume Laravel might return error on 'email' since that's what failed validation.
    // BUT, the user only sent 'identifier'.
    $response->assertSessionHasErrors(['email']);
});
