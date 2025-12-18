<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>{{ $pageTitle }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Auto-detect browser theme preference on first visit --}}
@if (isset($currentTheme) && $currentTheme === 'light')
    <script>
        (function() {
            // Only detect if theme is still default (no preference set)
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const detectedTheme = prefersDark ? 'dark' : 'light';

            // Only apply if different from default (light)
            if (detectedTheme === 'dark') {
                // Apply theme immediately (no refresh needed)
                document.documentElement.setAttribute('data-theme', detectedTheme);

                // Set cookie for server-side detection on next request
                // Cookie expires in 1 year
                const expires = new Date();
                expires.setFullYear(expires.getFullYear() + 1);
                document.cookie = '_preferred_theme=' + detectedTheme + '; path=/; expires=' + expires.toUTCString() +
                    '; SameSite=Lax';

                // Save to server in background (non-blocking)
                fetch('{{ route('preferences.theme') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: 'theme=' + encodeURIComponent(detectedTheme),
                    credentials: 'same-origin',
                }).catch(() => {
                    // Silently fail if request fails - cookie is set, will be picked up on next request
                });
            }
        })();
    </script>
@endif
