<?php

use App\Support\Csp\LaravelViteNonceGenerator;
use App\Support\Csp\MyCspPreset;
use Spatie\Csp\Presets\BunnyFonts;
use Spatie\Csp\Presets\GoogleFonts;

// use Spatie\Csp\Directive;
// use Spatie\Csp\Keyword;

return [
    /*
     * Presets will determine which CSP headers will be set. A valid CSP preset is
     * any class that implements `Spatie\Csp\Preset`
     */
    'presets' => [
        // Using our comprehensive standalone preset (replaces Basic)
        BunnyFonts::class,
        GoogleFonts::class,
        MyCspPreset::class,
    ],

    /**
     * Register additional global CSP directives here.
     */
    'directives' => [
        // [Directive::SCRIPT, [Keyword::UNSAFE_EVAL, Keyword::UNSAFE_INLINE]],
    ],

    /*
     * Vendor dashboards often ship precompiled single-page applications that
     * cannot receive this app's Blade nonce directives. Keep those exceptions
     * route-scoped so the main application remains under the stricter policy.
     */
    'third_party_dashboards' => [
        // [
        //     'name' => 'vendor-dashboard',
        //     'path' => config('vendor-dashboard.path'),
        //     'allow_inline_scripts' => true,
        //     'allow_unsafe_eval' => false,
        // ],
    ],

    /*
     * These presets which will be put in a report-only policy. This is great for testing out
     * a new policy or changes to existing CSP policy without breaking anything.
     */
    'report_only_presets' => [
        //
    ],

    /**
     * Register additional global report-only CSP directives here.
     */
    'report_only_directives' => [
        // [Directive::SCRIPT, [Keyword::UNSAFE_EVAL, Keyword::UNSAFE_INLINE]],
    ],

    /*
     * All violations against a policy will be reported to this url.
     * A great service you could use for this is https://report-uri.com/
     */
    'report_uri' => env('CSP_REPORT_URI', ''),

    /*
     * Headers will only be added if this setting is set to true.
     */
    'enabled' => true,

    /**
     * Headers will be added when Vite is hot reloading.
     */
    'enabled_while_hot_reloading' => true,

    /*
     * The class responsible for generating the nonces used in inline tags and headers.
     */
    'nonce_generator' => LaravelViteNonceGenerator::class,

    /*
     * Set false to disable automatic nonce generation and handling.
     * This is useful when you want to use 'unsafe-inline' for scripts/styles
     * and cannot add inline nonces.
     * Note that this will make your CSP policy less secure.
     */
    'nonce_enabled' => true,
];
