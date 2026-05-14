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
        $this->configureCurrentTenantDomainSources($policy);
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
            ->add(Directive::FONT, 'https://unpkg.com') // GrapeJS Fonts
            ->add(Directive::FONT, 'https://cdnjs.cloudflare.com') // FontAwesome

            // Form actions - prevents form hijacking
            ->add(Directive::FORM_ACTION, Keyword::SELF)

            // Frame sources (iframes)
            ->add(Directive::FRAME, Keyword::SELF)

            // Image sources - allow data: for inline images
            ->add(Directive::IMG, Keyword::SELF)
            ->add(Directive::IMG, Scheme::DATA)
            ->add(Directive::IMG, 'https://unpkg.com') // GrapeJS Images
            ->add(Directive::IMG, 'https://www.gravatar.com') // Gravatar Avatars
            ->add(Directive::IMG, 'https://secure.gravatar.com') // Secure Gravatar Avatars

            // Media sources
            ->add(Directive::MEDIA, Keyword::SELF)

            // Block all object/embed/applet (legacy plugins)
            ->add(Directive::OBJECT, Keyword::NONE);
    }

    /**
     * Allow only the currently resolved tenant's verified domains for passive
     * resources and network calls. Do not add tenant domains to script/style
     * execution sources.
     */
    protected function configureCurrentTenantDomainSources(Policy $policy): void
    {
        foreach ($this->currentTenantDomains() as $domain) {
            $policy
                ->add(Directive::IMG, $this->httpSourcesForDomain($domain))
                ->add(Directive::FRAME, $this->httpSourcesForDomain($domain))
                ->add(Directive::CONNECT, $this->connectSourcesForDomain($domain));
        }
    }

    protected function configureScriptsAndStyles(Policy $policy): void
    {
        // Script sources - use nonces for all scripts
        $policy
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->addNonce(Directive::SCRIPT)
            ->add(Directive::SCRIPT, 'https://unpkg.com') // GrapeJS CDN
            ->add(Directive::SCRIPT, 'https://cdn.jsdelivr.net'); // Chart.js CDN

        // Style sources - use nonces for all styles
        $policy
            ->add(Directive::STYLE, Keyword::SELF)
            ->addNonce(Directive::STYLE)
            ->add(Directive::STYLE, 'https://unpkg.com') // GrapeJS CDN
            ->add(Directive::STYLE, 'https://cdnjs.cloudflare.com') // FontAwesome for GrapeJS
            ->add(Directive::STYLE, 'https://fonts.googleapis.com') // Google Fonts
            ->add(Directive::STYLE, 'https://fonts.bunny.net'); // Bunny Fonts

        if (isLocal()) {
            // Livewire/Alpine often use inline styles that might not be captured by nonces in all environments
            $policy->add(Directive::STYLE, Keyword::UNSAFE_INLINE);
        }

        // Allow style elements created by rich editors and style/script attributes used by Alpine.js.
        // `style-src` nonces cause browsers to ignore `unsafe-inline`, so style elements need
        // an explicit directive when third-party editors inject runtime <style> tags.
        $policy
            ->add(Directive::STYLE_ELEM, Keyword::SELF)
            ->add(Directive::STYLE_ELEM, Keyword::UNSAFE_INLINE)
            ->add(Directive::STYLE_ELEM, 'https://unpkg.com')
            ->add(Directive::STYLE_ELEM, 'https://cdnjs.cloudflare.com')
            ->add(Directive::STYLE_ELEM, 'https://fonts.googleapis.com')
            ->add(Directive::STYLE_ELEM, 'https://fonts.bunny.net')
            ->add(Directive::STYLE_ATTR, Keyword::UNSAFE_INLINE)
            ->add(Directive::SCRIPT_ATTR, Keyword::UNSAFE_INLINE);

        // Vite dev server (local only)
        if (isLocal()) {
            $policy->add(Directive::SCRIPT, Keyword::UNSAFE_EVAL);

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
        $policy->add(Directive::CONNECT, Keyword::SELF)
            ->add(Directive::CONNECT, 'https://unpkg.com') // GrapeJS Maps
            ->add(Directive::CONNECT, 'https://app.grapesjs.com') // GrapeJS Telemetry
            ->add(Directive::CONNECT, 'https://cdn.jsdelivr.net'); // Chart.js Source Maps

        if (isLocal()) {
            // Allow central domains in local development to avoid blocking Vite/HMR on tenant domains
            foreach (config('tenancy.central_domains', []) as $domain) {
                if ($domain) {
                    $policy
                        ->add(Directive::CONNECT, "http://{$domain}")
                        ->add(Directive::CONNECT, "ws://{$domain}")
                        ->add(Directive::SCRIPT, $domain)
                        ->add(Directive::STYLE, $domain);
                }
            }

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

    /**
     * Get normalized domains for the current tenant only.
     *
     * @return array<int, string>
     */
    protected function currentTenantDomains(): array
    {
        if (! \function_exists('tenant') || ! tenant()) {
            return [];
        }

        return tenant()
            ->domains()
            ->pluck('domain')
            ->map(fn (string $domain) => $this->normalizeDomain($domain))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function httpSourcesForDomain(string $domain): array
    {
        return \array_values(\array_unique(\array_filter([
            $this->requestScheme() . "://{$domain}",
            isLocal() ? "http://{$domain}" : null,
            isLocal() ? "https://{$domain}" : null,
        ])));
    }

    /**
     * @return array<int, string>
     */
    protected function connectSourcesForDomain(string $domain): array
    {
        return \array_values(\array_unique(\array_filter([
            ...$this->httpSourcesForDomain($domain),
            $this->webSocketScheme() . "://{$domain}",
            isLocal() ? "ws://{$domain}" : null,
            isLocal() ? "wss://{$domain}" : null,
        ])));
    }

    protected function normalizeDomain(string $domain): ?string
    {
        $domain = \trim($domain);

        if ($domain === '') {
            return null;
        }

        if (\str_contains($domain, '://')) {
            $host = \parse_url($domain, PHP_URL_HOST);

            return \is_string($host) && $host !== '' ? $host : null;
        }

        return \rtrim($domain, '/');
    }

    protected function requestScheme(): string
    {
        return request()->secure() ? 'https' : 'http';
    }

    protected function webSocketScheme(): string
    {
        return request()->secure() ? 'wss' : 'ws';
    }
}
