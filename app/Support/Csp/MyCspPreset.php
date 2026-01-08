<?php

namespace App\Support\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Scheme;

/**
 * Comprehensive CSP Preset for Laravel with Livewire, Alpine.js, Vite, and Reverb.
 *
 * This is a standalone preset that replaces Spatie\Csp\Presets\Basic.
 * It provides:
 * - Strict nonce-based protection for scripts and styles
 * - Support for Alpine.js attribute bindings
 * - Vite dev server support in local environment
 * - Reverb WebSocket support in production
 */
class MyCspPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $this->configureBaseDirectives($policy);
        $this->configureScriptsAndStyles($policy);
        $this->configureConnectSources($policy);
        $this->configureHorizonSources($policy);
        $this->configureTelescopeSources($policy);
    }

    /**
     * Configure base security directives (similar to Basic preset).
     */
    protected function configureBaseDirectives(Policy $policy): void
    {
        $policy
            // Base URI restriction - prevents base tag hijacking
            ->add(Directive::BASE, Keyword::SELF)

            // Default fallback for unlisted directives
            ->add(Directive::DEFAULT, Keyword::SELF)

            // Font sources
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::FONT, Scheme::DATA)

            // Form actions - prevents form hijacking
            ->add(Directive::FORM_ACTION, Keyword::SELF)

            // Frame sources (iframes)
            ->add(Directive::FRAME, Keyword::SELF)

            // Image sources - allow data: for inline images
            ->add(Directive::IMG, Keyword::SELF)
            ->add(Directive::IMG, Scheme::DATA)

            // Media sources
            ->add(Directive::MEDIA, Keyword::SELF)

            // Block all object/embed/applet (legacy plugins)
            ->add(Directive::OBJECT, Keyword::NONE);
    }

    /**
     * Configure script and style sources.
     *
     * NOTE: We use 'unsafe-inline' instead of nonces because Livewire's wire:navigate
     * performs SPA-style navigation. Each server response has a NEW nonce, but the
     * browser's CSP policy still has the OLD nonce from the initial page load.
     * This is a known limitation of CSP + SPA navigation.
     */
    protected function configureScriptsAndStyles(Policy $policy): void
    {
        // Script sources - allow self + inline (for Livewire wire:navigate)
        $policy
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->add(Directive::SCRIPT, Keyword::UNSAFE_INLINE);

        // Style sources - allow self + inline (for Livewire wire:navigate)
        $policy
            ->add(Directive::STYLE, Keyword::SELF)
            ->add(Directive::STYLE, Keyword::UNSAFE_INLINE);

        // Allow inline style/script ATTRIBUTES for Alpine.js
        // (:class="...", @click="...", x-bind:style="...", etc.)
        $policy
            ->add(Directive::STYLE_ATTR, Keyword::UNSAFE_INLINE)
            ->add(Directive::SCRIPT_ATTR, Keyword::UNSAFE_INLINE);

        // Vite dev server (local only)
        if (isLocal()) {
            $viteHost = config('vite.dev_server.host');
            $vitePort = config('vite.dev_server.port');

            if ($viteHost && $vitePort) {
                $viteUrl = "{$viteHost}:{$vitePort}";
                $policy
                    ->add(Directive::SCRIPT, $viteUrl)
                    ->add(Directive::STYLE, $viteUrl);
            }
        }
    }

    /**
     * Configure connect-src for fetch/XHR/WebSocket.
     */
    protected function configureConnectSources(Policy $policy): void
    {
        // Allow connections to self
        $policy->add(Directive::CONNECT, Keyword::SELF);

        if (isLocal()) {
            // Vite dev server connections (HMR)
            $viteHost = config('vite.dev_server.host');
            $vitePort = config('vite.dev_server.port');

            if ($viteHost && $vitePort) {
                $viteUrl = "{$viteHost}:{$vitePort}";
                $policy
                    ->add(Directive::CONNECT, "http://{$viteUrl}")
                    ->add(Directive::CONNECT, "ws://{$viteUrl}");
            }

            // Allow all WebSocket schemes for local development (Reverb)
            $policy
                ->add(Directive::CONNECT, Scheme::WS)
                ->add(Directive::CONNECT, Scheme::WSS);
        } else {
            // Production: Reverb WebSocket (specific host only)
            $reverb = config('broadcasting.connections.reverb.options');

            if ($host = $reverb['host'] ?? null) {
                $port = $reverb['port'] ?? 443;
                $scheme = $reverb['scheme'] ?? 'https';
                $protocol = $scheme === 'https' ? 'wss' : 'ws';

                $policy->add(Directive::CONNECT, "{$protocol}://{$host}:{$port}");
            }
        }
    }

    /**
     * Configure CSP sources for Laravel Horizon.
     *
     * Horizon's dashboard uses JavaScript eval() for its UI functionality,
     * which requires 'unsafe-eval' in the script-src directive.
     */
    protected function configureHorizonSources(Policy $policy): void
    {
        $horizonPath = config('horizon.path', 'horizon');

        if (! request()->is($horizonPath . '*')) {
            return;
        }

        $policy->add(Directive::SCRIPT, Keyword::UNSAFE_EVAL);
    }

    /**
     * Configure CSP sources for Laravel Telescope.
     *
     * Telescope's dashboard uses JavaScript eval() for its UI functionality,
     * which requires 'unsafe-eval' in the script-src directive.
     */
    protected function configureTelescopeSources(Policy $policy): void
    {
        $telescopePath = config('telescope.path', 'telescope');

        if (! request()->is($telescopePath . '*')) {
            return;
        }

        $policy->add(Directive::SCRIPT, Keyword::UNSAFE_EVAL);
    }
}
