@props([
    'layout' => 'app',
])

<meta charset="utf-8" />
<meta name="viewport"
      content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token"
      content="{{ csrf_token() }}" />

<title>{{ $pageTitle }} - {{ config('app.name') }}</title>

<link rel="icon"
      href="/favicon.ico"
      sizes="any" />
<link rel="icon"
      href="/favicon.svg"
      type="image/svg+xml" />
<link rel="apple-touch-icon"
      href="/apple-touch-icon.png" />
@stack('beginHead')

<script type="text/javascript"
        @cspNonce>
    window.notificationRealtimeConfig = @js($notificationRealtimeConfig);
</script>

@php
    $cssFile = config("assets.css.{$layout}", config('assets.css.app'));
    $sharedJs = config('assets.js.shared', []);
    $layoutJs = config("assets.js.{$layout}", []);
    $allAssets = array_merge([$cssFile], $sharedJs, $layoutJs);
@endphp

@vite($allAssets)

@livewireStyles(['nonce' => cspNonce()])

@stack('endHead')
