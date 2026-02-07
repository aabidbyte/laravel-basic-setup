<?php

use App\Models\User;
use Livewire\Livewire;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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
