<?php

use App\Support\Csp\LaravelViteNonceGenerator;
use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Directive;
use Spatie\Csp\Policy;

test('csp nonce generator initializes the vite nonce used by policy headers', function (): void {
    $nonce = app(LaravelViteNonceGenerator::class)->generate();

    $policy = new Policy();
    $policy->add(Directive::SCRIPT, "'self'");
    $policy->addNonce(Directive::SCRIPT);

    expect($nonce)
        ->not->toBe('')
        ->toBe(Vite::cspNonce())
        ->and($policy->getContents())
        ->toContain("'nonce-{$nonce}'");
});
