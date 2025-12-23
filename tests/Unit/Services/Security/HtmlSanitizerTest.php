<?php

declare(strict_types=1);

use App\Services\Security\HtmlSanitizer;

it('removes script tags', function () {
    $sanitizer = new HtmlSanitizer;
    $html = '<p>Hello</p><script>alert("xss")</script>';

    $result = $sanitizer->sanitize($html);

    expect($result)->not->toContain('<script>');
    expect($result)->toContain('<p>Hello</p>');
});

it('removes event handlers', function () {
    $sanitizer = new HtmlSanitizer;
    $html = '<p onclick="alert(\'xss\')">Hello</p>';

    $result = $sanitizer->sanitize($html);

    expect($result)->not->toContain('onclick');
});

it('removes javascript: URLs', function () {
    $sanitizer = new HtmlSanitizer;
    $html = '<a href="javascript:alert(\'xss\')">Click</a>';

    $result = $sanitizer->sanitize($html);

    expect($result)->toContain('href="#"');
    expect($result)->not->toContain('javascript:');
});

it('preserves allowed tags', function () {
    $sanitizer = new HtmlSanitizer;
    $html = '<p><strong>Bold</strong> and <em>italic</em></p>';

    $result = $sanitizer->sanitize($html);

    expect($result)->toContain('<p>');
    expect($result)->toContain('<strong>');
    expect($result)->toContain('<em>');
});

it('removes disallowed attributes', function () {
    $sanitizer = new HtmlSanitizer;
    $html = '<p onmouseover="alert(1)" class="allowed">Hello</p>';

    $result = $sanitizer->sanitize($html);

    expect($result)->not->toContain('onmouseover');
    expect($result)->toContain('class="allowed"');
});

it('checks if HTML is safe', function () {
    $sanitizer = new HtmlSanitizer;

    expect($sanitizer->isSafe('<p>Safe</p>'))->toBeTrue();
    expect($sanitizer->isSafe('<p>Safe</p><script>alert(1)</script>'))->toBeFalse();
});
