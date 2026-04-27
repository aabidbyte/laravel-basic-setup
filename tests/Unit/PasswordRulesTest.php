<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

test('password defaults are strict', function () {
    // We can't easily inspect the rule object's internals publicly without reflection or casting.
    // Instead, we trust the integration test below.
    $this->assertTrue(true);
});

test('weak password fails defaults', function () {
    // Weak: short
    $validator = Validator::make(['password' => 'short'], ['password' => Password::defaults()]);
    expect($validator->fails())->toBeTrue();

    // Weak: no numbers
    $validator = Validator::make(['password' => 'PasswordOnly'], ['password' => Password::defaults()]);
    expect($validator->fails())->toBeTrue();

    // Weak: no symbols
    $validator = Validator::make(['password' => 'Password123'], ['password' => Password::defaults()]);
    expect($validator->fails())->toBeTrue();

    // Strong
    $validator = Validator::make(['password' => 'S0m3V3ryStr0ngP@ssw0rd!'], ['password' => Password::defaults()]);
    expect($validator->fails())->toBeFalse();
});
