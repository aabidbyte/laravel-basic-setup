<?php

use App\Models\User;
use Database\Seeders\TenantSeeders\Production\EmailTemplateSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Livewire\Livewire;

beforeEach(function () {
    asTenant();
    $this->seed(EmailTemplateSeeder::class);
    Mail::fake();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.account')
        ->set('name', 'Test Name')
        ->set('email', 'test@example.com')
        ->set('username', 'testuser')
        ->call('updateProfileInformation');

    $user->refresh();

    expect($user->name)->toEqual('Test Name');
    expect($user->email)->toEqual('test@example.com');
    expect($user->username)->toEqual('testuser');
});

test('username must be unique', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create(['username' => 'taken']);

    $this->actingAs($user);

    Livewire::test('pages::settings.account')
        ->set('name', 'Test Name')
        ->set('email', 'test@example.com')
        ->set('username', 'taken')
        ->call('updateProfileInformation')
        ->assertHasErrors(['username']);
});

test('email must be unique', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($user);

    Livewire::test('pages::settings.account')
        ->set('name', 'Test Name')
        ->set('email', 'taken@example.com')
        ->set('username', 'testuser')
        ->call('updateProfileInformation')
        ->assertHasErrors(['email']);
});

test('current password validation uses semantic translation key', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    try {
        app(UpdatesUserPasswords::class)->update($user, [
            'current_password' => 'wrong-password',
            'password' => 'New-password-123!',
            'password_confirmation' => 'New-password-123!',
        ]);
    } catch (ValidationException $exception) {
        expect($exception->errors()['current_password'][0])
            ->toBe(__('settings.password.current_password_mismatch'));

        return;
    }

    $this->fail('Expected current password validation to fail.');
});
