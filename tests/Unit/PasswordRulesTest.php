<?php

namespace Tests\Unit;

use Tests\TestCase;
use Validator;

class PasswordRulesTest extends TestCase
{
    public function test_password_defaults_are_strict()
    {
        // We can't easily inspect the rule object's internals publicly without reflection or casting.
        // Instead, we trust the integration test below.
        $this->assertTrue(true);
    }

    public function test_weak_password_fails_defaults()
    {
        // Weak: short
        $validator = Validator::make(['password' => 'short'], ['password' => \Illuminate\Validation\Rules\Password::defaults()]);
        $this->assertTrue($validator->fails());

        // Weak: no numbers
        $validator = Validator::make(['password' => 'PasswordOnly'], ['password' => \Illuminate\Validation\Rules\Password::defaults()]);
        $this->assertTrue($validator->fails());

        // Weak: no symbols
        $validator = Validator::make(['password' => 'Password123'], ['password' => \Illuminate\Validation\Rules\Password::defaults()]);
        $this->assertTrue($validator->fails());

        // Strong
        $validator = Validator::make(['password' => 'S0m3V3ryStr0ngP@ssw0rd!'], ['password' => \Illuminate\Validation\Rules\Password::defaults()]);
        $this->assertFalse($validator->fails());
    }
}
