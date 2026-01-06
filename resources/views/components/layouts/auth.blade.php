<!DOCTYPE html>
<html
    lang="{{ $htmlLangAttribute }}"
    dir="{{ $htmlDirAttribute }}"
    data-theme="{{ $currentTheme }}"
>

<head>
    @include('partials.head', ['layout' => 'auth'])
</head>

<body class="min-h-screen bg-base-100">
    <x-layouts.auth.split>
        {{ $slot }}
    </x-layouts.auth.split>

    @include('partials.end-body', ['layout' => 'auth'])

    @stack('endBody')
</body>

</html>
