<!DOCTYPE html>
<html
    lang="{{ $htmlLangAttribute }}"
    dir="{{ $htmlDirAttribute }}"
    data-theme="{{ $currentTheme }}"
>

<head>
    @include('partials.head', ['layout' => 'app'])
</head>

<body>
    <x-layouts.app.sidebar>
        {{ $slot }}
    </x-layouts.app.sidebar>

    @include('partials.end-body', ['layout' => 'app'])

    @stack('endBody')
</body>

</html>
