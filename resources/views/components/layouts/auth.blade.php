<!DOCTYPE html>
<html lang="{{ $htmlLangAttribute }}"
      dir="{{ $htmlDirAttribute }}"
      data-theme="{{ $currentTheme }}">

    <head>
        @include('partials.head', ['layout' => 'auth'])
    </head>

    <body class="bg-base-100 min-h-screen">
        <x-layouts.auth.split>
            {{ $slot }}
        </x-layouts.auth.split>

        @include('partials.end-body', ['layout' => 'auth'])

        @stack('endBody')
    </body>

</html>
